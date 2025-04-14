<?php

namespace App\Listeners;

use App\Jobs\SendTransactionEmailJob;
use Illuminate\Auth\Events\Registered;

class SignupWelcomeEmail
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {

        $mailParms = [
            'email' => $event->user->email,
            'name' => $event->user->name
        ];

        dispatch(new SendTransactionEmailJob((object) $mailParms, 'signup_welcome_email'));
    }
}
