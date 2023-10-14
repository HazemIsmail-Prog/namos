<?php

namespace App\Filament\Admin\Resources\AppointmentResource\Pages;

use App\Filament\Admin\Resources\AppointmentResource;
use DateTime;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $start = new DateTime($data['start']);
    //     $data['creator_id'] = auth()->id();
    //     $data['end'] = $start->format('H:i') +30;
    //     dd($data);
    //     return $data;
    // }
}
