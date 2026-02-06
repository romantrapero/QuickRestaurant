<?php

namespace App\Filament\Resources\Tables\Pages;

use App\Filament\Resources\Tables\TableResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTable extends CreateRecord
{
    protected static string $resource = TableResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se especifica orden_visual, usar el siguiente disponible
        if (empty($data['orden_visual'])) {
            $data['orden_visual'] = \App\Models\Table::max('orden_visual') + 1;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
