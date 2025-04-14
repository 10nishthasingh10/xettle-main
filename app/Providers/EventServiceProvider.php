<?php

namespace App\Providers;

use App\Listeners\SignupWelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use \Spatie\WebhookServer\Events\WebhookCallFailedEvent;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SignupWelcomeEmail::class,
            SendEmailVerificationNotification::class,
        ],
        'Spatie\WebhookServer\Events\WebhookCallSucceededEvent' => [
            'App\Listeners\WebhookCallSuccess',
        ],
        'Spatie\WebhookServer\Events\WebhookCallFailedEvent' => [
            'App\Listeners\WebhookCallFailed',
        ],
        'Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent' => [
            'App\Listeners\WebhookFinalCallFailed',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
