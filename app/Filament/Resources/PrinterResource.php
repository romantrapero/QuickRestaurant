<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrinterResource\Pages;
use App\Models\Printer;
use App\Services\PrinterService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class PrinterResource extends Resource
{
    protected static ?string $model = Printer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-printer';

    protected static ?string $navigationLabel = 'Impresoras';

    protected static ?string $modelLabel = 'Impresora';

    protected static ?string $pluralModelLabel = 'Impresoras';

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Impresora Barra Fría'),

                Select::make('station')
                    ->label('Estación')
                    ->options(Printer::getStationOptions())
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('connection_type')
                    ->label('Tipo de Conexión')
                    ->options(Printer::getConnectionOptions())
                    ->required()
                    ->live(),

                TextInput::make('usb_path')
                    ->label('Path USB')
                    ->placeholder('/dev/usb/lp0')
                    ->helperText('Ej: /dev/usb/lp0 en Linux')
                    ->visible(fn (Get $get) => $get('connection_type') === 'usb')
                    ->required(fn (Get $get) => $get('connection_type') === 'usb'),

                TextInput::make('network_ip')
                    ->label('Dirección IP')
                    ->placeholder('192.168.1.100')
                    ->visible(fn (Get $get) => $get('connection_type') === 'network')
                    ->required(fn (Get $get) => $get('connection_type') === 'network'),

                TextInput::make('network_port')
                    ->label('Puerto')
                    ->numeric()
                    ->default(9100)
                    ->visible(fn (Get $get) => $get('connection_type') === 'network'),

                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('station')
                    ->label('Estación')
                    ->formatStateUsing(fn (string $state): string => Printer::getStationOptions()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cold_bar' => 'info',
                        'hot_bar' => 'danger',
                        'cashier' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('connection_type')
                    ->label('Conexión')
                    ->formatStateUsing(fn (string $state): string => $state === 'usb' ? 'USB' : 'Red'),

                TextColumn::make('connection_info')
                    ->label('Dirección')
                    ->getStateUsing(function (Printer $record): string {
                        if ($record->connection_type === 'usb') {
                            return $record->usb_path ?? '-';
                        }

                        return ($record->network_ip ?? '-').':'.$record->network_port;
                    }),

                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('station')
                    ->label('Estación')
                    ->options(Printer::getStationOptions()),

                TernaryFilter::make('is_active')
                    ->label('Activa'),
            ])
            ->actions([
                Action::make('test')
                    ->label('Probar')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (Printer $record) {
                        $service = new PrinterService;
                        $success = $service->testPrinter($record);

                        if ($success) {
                            Notification::make()
                                ->title('Impresora OK')
                                ->body('La impresora respondió correctamente.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error de Impresora')
                                ->body('No se pudo conectar a la impresora. Revisa la configuración.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrinters::route('/'),
            'create' => Pages\CreatePrinter::route('/create'),
            'edit' => Pages\EditPrinter::route('/{record}/edit'),
        ];
    }
}
