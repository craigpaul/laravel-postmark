<?php

namespace Coconuts\Mail;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

class PostmarkServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'postmark');

        $this->publishes([
            __DIR__.'/../config/postmark.php' => config_path('postmark.php'),
        ], 'config');

        if ($this->app['config']['mail.driver'] !== 'postmark') {
            return;
        }

        $this->mergeConfigFrom(__DIR__.'/../config/postmark.php', 'postmark');

        $this->app['swift.transport']->extend('postmark', function () {
            return new PostmarkTransport(
                $this->guzzle(config('postmark.guzzle', [])),
                config('postmark.secret', config('services.postmark.secret'))
            );
        });
    }

    /**
     * Get a fresh Guzzle HTTP client instance.
     *
     * @param  array  $config
     * @return \GuzzleHttp\Client
     */
    protected function guzzle($config)
    {
        return new HttpClient($config);
    }
}
