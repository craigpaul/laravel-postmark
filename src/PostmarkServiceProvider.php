<?php

namespace CraigPaul\Mail;

use Illuminate\Support\ServiceProvider;

class PostmarkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'postmark');

        $this->app['mail.manager']->extend('postmark', function () {
            return new PostmarkTransport();
        });
    }
}
