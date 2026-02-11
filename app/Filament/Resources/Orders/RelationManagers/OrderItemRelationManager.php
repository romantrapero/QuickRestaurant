<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Dish;
use App\Models\OrderModification;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class OrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('dish_id')
                ->label('Platillo')
                ->options(Dish::where('is_available', true)->pluck('name', 'id'))
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $dish = Dish::find($state);
                        $set('unit_price', $dish?->sale_price ?? 0);
                    }
                }),
            TextInput::make('quantity')
                ->label('Cantidad')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),
            TextInput::make('unit_price')
                ->label('Precio Unitario')
                ->numeric()
                ->prefix('$')
                ->required(),
            Textarea::make('special_instructions')
                ->label('Notas Especiales')
                ->placeholder('Sin cebolla, término medio, etc.')
                ->rows(3)
                ->maxLength(500),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('dish.name')->label('Platillo'),
                TextColumn::make('quantity')->label('Cantidad'),
                TextColumn::make('unit_price')->label('Precio Unitario')->money('MXN'),
                TextColumn::make('total_price')->label('Total')->money('MXN'),
                TextColumn::make('special_instructions')
                    ->label('Notas')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }

                        return null;
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar Item')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['total_price'] = $data['unit_price'] * $data['quantity'];

                        return $data;
                    })
                    ->after(function ($record) {
                        $order = $record->order;
                        $totalBefore = (float) $order->total;
                        $order->recalculateTotal();

                        OrderModification::create([
                            'order_id' => $order->id,
                            'order_item_id' => $record->id,
                            'type' => 'item_added',
                            'dish_name' => $record->dish->name,
                            'quantity' => $record->quantity,
                            'unit_price' => $record->unit_price,
                            'total_before' => $totalBefore,
                            'total_after' => $order->total,
                            'modified_by' => auth()->user()?->name ?? 'Admin',
                        ]);
                    }),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('Eliminar')
                    ->before(function ($record) {
                        $order = $record->order;
                        $totalBefore = (float) $order->total;

                        OrderModification::create([
                            'order_id' => $order->id,
                            'order_item_id' => $record->id,
                            'type' => 'item_removed',
                            'dish_name' => $record->dish->name,
                            'quantity' => $record->quantity,
                            'unit_price' => $record->unit_price,
                            'total_before' => $totalBefore,
                            'total_after' => $totalBefore - ($record->unit_price * $record->quantity),
                            'modified_by' => auth()->user()?->name ?? 'Admin',
                        ]);
                    })
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ]);
    }
}
