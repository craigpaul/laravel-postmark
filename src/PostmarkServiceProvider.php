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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'postmark');

        $this->app['mail.manager']->extend('postmark', function ($config) {
            $config = $this->app->make('config');

            return new PostmarkTransport(
                $this->app->make(Factory::class),
                $config['message_stream_id'] ?? null,
                $config->get('services.postmark.options', []),
                $config->get('services.postmark.token'),
            );
        });
    }
}
