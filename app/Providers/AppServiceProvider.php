<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Events\EmailSent;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $invalidEmails = [];
        Event::listen(EmailSent::class, function (EmailSent $event) use (&$invalidEmails) {
            if (!$event->success) {
                $invalidEmails[] = ['email' => $event->email, 'message' => $event->errorMessage];
            }
        });

        view()->share('invalidEmails', $invalidEmails);
    }
}
