<x-filament-panels::page>
    <div wire:ignore id='calendar'></div>
    
    <link rel="stylesheet" href="{{ asset('plugins\fullcalendar\main.min.css') }}">

    <script src='{{ asset('plugins\fullcalendar\main.min.js') }}'></script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            resources = {!! json_encode($resources) !!};
            events = {!! json_encode($events) !!};
            date = {!! json_encode($date) !!}
            loadCalendar(resources, events, date);
        })

        window.addEventListener('render-calendar', event => {
            resources = event.detail[0].resources;
            events = event.detail[0].events;
            date = event.detail[0].date;
            loadCalendar(resources, events, date);
        });

        function loadCalendar(resources, events, date) {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next today'
                },
                titleFormat: { // will produce something like "Tuesday, September 18, 2018"
                    month: 'long',
                    year: 'numeric',
                    day: 'numeric',
                    weekday: 'long'
                },
                initialDate: date,
                themeSystem: 'pulse',
                displayEventTime: false,
                timeZone: 'Asia/Baghdad',
                initialView: 'resourceTimeGridDay',
                slotDuration: '00:30',
                slotMinTime: '08:00',
                slotMaxTime: '23:59',
                // scrollTime: '13:00:00',
                height: 'auto',
                handleWindowResize: true,
                eventOverlap: false, // will cause the event to take up entire resource height
                nowIndicator: true,
                allDaySlot: false,
                selectMirror: true,
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short'
                },
                // resourceOrder: 'sort', // when title tied, order by id
                resources: resources,
                events: events,
                eventChange: function(arg) {
                    if (confirm('Edit selected appointment.\nAre you sure ?')) {
                        var id = arg.event.id;
                        var startTime = arg.event.startStr.substr(11);
                        var endTime = arg.event.endStr.substr(11);
                        var device = parseInt(arg.event._def.resourceIds);

                        @this.quick_edit(id, startTime, endTime, device);
                    } else {
                        @this.getData();
                    }
                },
                eventClick: function(arg) {
                    var id = arg.event.id;
                    @this.set('appointment_id', id);
                    @this.mountAction('edit')
                },
                datesSet: function(arg) {
                    if (arg.startStr.substr(0, 10) != date.substr(0, 10)) {
                        @this.set('date',arg.startStr);
                        @this.getData(arg.startStr);
                    }
                },
                editable: true,
                select: function(arg) {
                    var resource = arg.resource.id;
                    var device_name = arg.resource.title;
                    var day = arg.startStr.substr(8, 2);
                    var month = arg.startStr.substr(5, 2);
                    var year = arg.startStr.substr(0, 4);
                    var fulldate = year + '-' + month + '-' + day
                    var startTime = arg.startStr.substr(11);
                    var endTime = arg.endStr.substr(11);
                    var data = {
                        staff_id: resource,
                        date: fulldate,
                        start: startTime,
                        end: endTime,
                    };
                    @this.set('data', data);
                    @this.mountAction('create')
                },
                selectable: true,
            });
            calendar.render();
        }
    </script>
</x-filament-panels::page>
