<?php

namespace App\Filament\Resources\RestaurantConfigResource\Pages;

use App\Filament\Resources\RestaurantConfigResource;
use App\Models\RestaurantConfig;
use App\Models\Table;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ManageRestaurantConfig extends EditRecord
{
    protected static string $resource = RestaurantConfigResource::class;

    protected static ?string $title = 'Configuración del Restaurante';

    public function mount(int|string|null $record = null): void
    {
        $this->record = RestaurantConfig::config();
        $this->fillForm();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Configuración guardada correctamente';
    }

    /*protected function mutateFormDataBeforeSave(array $data): array
    {
        $existingTables = Table::count();

        if ($data['numero_mesas'] < $existingTables) {
            Notification::make()
                ->warning()
                ->title('Advertencia')
                ->body("Ya existen {$existingTables} mesas creadas. No puedes reducir el número por debajo de eso.")
                ->send();

            $data['numero_mesas'] = $existingTables;
        }

        return $data;
    }*/
}
