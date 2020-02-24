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
        ], 'postmark-config');

        $this->mergeConfigFrom(__DIR__.'/../config/postmark.php', 'postmark');

        $this->registerPostmarkDriver();
    }

    /**
     * Register the Postmark driver.
     *
     * @return void
     */
    private function registerPostmarkDriver()
    {
        if (! $this->shouldRegisterPostmarkDriver()) {
            return;
        }

        $this->app['swift.transport']->extend('postmark', function () {
            return new PostmarkTransport(
                $this->guzzle(config('postmark.guzzle', [])),
                config('postmark.secret', config('services.postmark.secret'))
            );
        });
    }

    /**
     * Determine if we should register the Postmark driver.
     *
     * @return bool
     */
    protected function shouldRegisterPostmarkDriver()
    {
        return $this->app['config']['mail.driver'] === 'postmark';
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
