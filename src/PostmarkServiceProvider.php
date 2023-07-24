<?php

namespace CraigPaul\Mail;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;

class PostmarkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'postmark');

        $this->app['mail.manager']->extend('postmark', function ($config) {
            return new PostmarkTransport(
                $this->app->make(Factory::class),
                $this->app->make('config')->get('services.postmark.message_stream_id'),
                $this->app->make('config')->get('services.postmark.token'),
            );
        });
    }
}
