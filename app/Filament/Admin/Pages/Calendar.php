<?php

namespace App\Filament\Admin\Pages;

use App\Models\Appointment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Pages\Page;

class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.admin.pages.calendar';

    public function createAction(): Action
    {
        return Action::make('create')
            ->label('Create New Appointment')
            ->form(
                fn () =>
                [
                    Radio::make('status')
                        ->options([
                            'waiting' => 'Waiting',
                            'confirmed' => 'Confirmed',
                            'cancelled' => 'Cancelled',
                            'off' => 'Off',
                        ])
                        ->live()
                        ->default('waiting')
                        ->inline()
                        ->required()
                        ->columnSpanFull(),
                    Select::make('customer_id')
                        ->label('Customer')
                        ->required()
                        ->options(User::where('role_id', 3)->pluck('name', 'id'))
                        ->searchable(),
                    Select::make('staff_id')
                        ->label('Staff')
                        ->required()
                        ->options(User::where('role_id', 2)->pluck('name', 'id'))
                        ->searchable()
                        ->default($this->data['staff_id']),
                    DatePicker::make('date')
                        ->required()
                        ->afterOrEqual(today())
                        ->native(false)
                        ->default($this->data['date'])
                        ->closeOnDateSelection(),
                    TimePicker::make('start')
                        ->seconds(false)
                        ->default($this->data['start']),
                    TimePicker::make('end')
                        ->seconds(false)
                        ->default($this->data['end']),
                ]
            )
            ->action(function (array $data): void {
                $data['creator_id'] = auth()->id();
                Appointment::create($data);
                $this->getData();
            })
            ->modalCancelAction(function () {
                $this->getData();
            })
            ->modalWidth('lg')
            ->slideOver();
    }


    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit Appointment')
            ->form(function () {
                $this->appointment = Appointment::find($this->appointment_id);
                return [
                    Radio::make('status')
                        ->options([
                            'waiting' => 'Waiting',
                            'confirmed' => 'Confirmed',
                            'cancelled' => 'Cancelled',
                            'off' => 'Off',
                        ])
                        ->live()
                        ->default($this->appointment->status)
                        ->inline()
                        ->required()
                        ->columnSpanFull(),
                    Select::make('customer_id')
                        ->label('Customer')
                        ->required()
                        ->options(User::where('role_id', 3)->pluck('name', 'id'))
                        ->searchable()
                        ->default($this->appointment->customer_id),
                    Select::make('staff_id')
                        ->label('Staff')
                        ->required()
                        ->options(User::where('role_id', 2)->pluck('name', 'id'))
                        ->searchable()
                        ->default($this->appointment->staff_id),
                    DatePicker::make('date')
                        ->required()
                        ->afterOrEqual(today())
                        ->native(false)
                        ->default($this->appointment->date)
                        ->closeOnDateSelection(),
                    TimePicker::make('start')
                        ->seconds(false)
                        ->before('end')
                        ->minutesStep(30)
                        ->default($this->appointment->start),
                    TimePicker::make('end')
                        ->seconds(false)
                        ->after('start')
                        ->minutesStep(30)
                        ->default($this->appointment->end),
                ];
            })
            ->action(function (array $data): void {
                $this->appointment->update($data);
                $this->getData($this->appointment->date);
            })
            ->modalCancelAction(function () {
                $this->getData($this->appointment->date);
            })
            ->modalWidth('lg')
            ->slideOver();
    }

    public $resources = [];
    public $events = [];
    public $date;
    public $appointment_id;
    public $appointment;
    public $data = [];

    public function mount()
    {
        $this->getData();
    }

    public function setDate($date)
    {
        $this->date = $date;
        $this->getData($this->date);
    }

    public function getData($date = null)
    {
        if (!$date) {
            $this->date = today();
        } else {
            $this->date = $date;
        }
        $this->resources = [];
        $this->events = [];
        $staffs = User::where('role_id', 2)->get();
        foreach ($staffs as $staff) {
            $this->resources[] = [
                'id' => $staff->id,
                'title' => $staff->name,
            ];
        }

        $appointments = Appointment::where('date', $this->date)->get();

        foreach ($appointments as $appointment) {
            $this->events[] = [
                'id' => $appointment->id,
                'title' => $appointment->customer->name,
                'start' => date('Y-m-d', strtotime($appointment->date)) . 'T' . $appointment->start,
                'end' => date('Y-m-d', strtotime($appointment->date)) . 'T' . $appointment->end,
                'resourceId' => $appointment->staff_id,
                // 'color' => $appointment->status->color,
            ];
        }

        $this->dispatch('render-calendar', ['resources' => $this->resources, 'events' => $this->events, 'date' => $this->date]);
    }

    // Drag & Drop Edit for Appointment -----------------------------
    public function quick_edit($id, $start, $end, $device)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->status_id == 6) {
            $data = [
                'start' => $start,
                'end' => $end,
                'device_id' => $device,
                'updated_by' => auth()->user()->id,
            ];
            $appointment->update($data);
        } else {
            $busy_staff = Appointment::query()
                ->where('date', $this->date)
                ->where('id', '!=', $appointment->id)
                ->where('status', '!=', 'cancelled')   // to ignore Cancelled
                ->whereNotNull('staff_id')
                ->where('staff_id', '!=', 0)
                ->where('staff_id', $appointment->staff_id)
                ->where(function ($q) use ($start, $end) {
                    $q->where('start', '>=', $start)
                        ->where('start', '<', $end)
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('end', '>', $start)
                                ->where('end', '<=', $end)
                                ->orWhere(function ($q) use ($start, $end) {
                                    $q->where('start', '<=', $start)
                                        ->where('end', '>=', $end);
                                });
                        });
                })
                ->first();


            if ($busy_staff == null) {
                $data = [
                    'start' => $start,
                    'end' => $end,
                    'staff_id' => $device,
                ];

                $appointment->update($data);
            } else {
                if ($busy_staff) {
                    $this->dispatchBrowserEvent('alert', [
                        'title' => '<strong  class="text-primary">Error</strong>',
                        'message' =>
                        $busy_staff->staff->name .
                            ' is busy on ' .
                            $busy_staff->app_device->name .
                            ' from ' .
                            date('h:i a', strtotime($busy_staff->start)) .
                            ' to ' .
                            date('h:i a', strtotime($busy_staff->end))
                    ]);
                }
            }
        }
        $this->getData();
    }


    public function print()
    {
        redirect()->route('appointments.print', ['date' => $this->date, 'department' => $this->app_department]);
    }
}
