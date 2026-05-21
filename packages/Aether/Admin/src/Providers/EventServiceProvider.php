<?php

namespace Aether\Admin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Las asignaciones del controlador de eventos para la aplicación.
     *
     * @var array
     */
    protected $listen = [
        'contacts.person.create.after' => [
            'Aether\Admin\Listeners\Person@linkToEmail',
        ],

        'lead.create.after' => [
            'Aether\Admin\Listeners\Lead@linkToEmail',
        ],

        'activity.create.after' => [
            'Aether\Admin\Listeners\Activity@afterUpdateOrCreate',
        ],

        'activity.update.after' => [
            'Aether\Admin\Listeners\Activity@afterUpdateOrCreate',
        ],
    ];
}
