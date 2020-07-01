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
    public function boot(): void
    {
        $this->registerPublishing();

        $this->mergeConfigFrom(__DIR__.'/../config/postmark.php', 'postmark');

        $this->registerPostmarkDriver();
    }

    /**
     * Register the Postmark driver.
     *
     * @return void
     */
    private function registerPostmarkDriver(): void
    {
        if (! $this->shouldRegisterPostmarkDriver()) {
            return;
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'postmark');
        $this->mergeConfigFrom(__DIR__.'/../config/postmark.php', 'postmark');

        $this->resolveTransportManager()->extend('postmark', function () {
            return new PostmarkTransport(
                $this->guzzle(config('postmark.guzzle', [])),
                config('postmark.secret', config('services.postmark.secret'))
            );
        });
    }

    /**
     * Resolve the mail manager.
     *
     * @return \Illuminate\Mail\TransportManager|\Illuminate\Mail\MailManager
     */
    public function resolveTransportManager()
    {
        if ($this->app->has('mail.manager')) {
            return $this->app['mail.manager'];
        }

        return $this->app['swift.transport'];
    }

    /**
     * Determine if we should register the Postmark driver.
     *
     * @return bool
     */
    protected function shouldRegisterPostmarkDriver(): bool
    {
        if ($this->app->has('mail.manager')) {
            return true;
        }

        return $this->app['config']['mail.driver'] === 'postmark';
    }

    /**
     * Register the publishable resources for this package.
     *
     * @return void
     */
    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/postmark.php' => config_path('postmark.php'),
            ], 'postmark-config');
        }
    }

    /**
     * Get a fresh Guzzle HTTP client instance.
     *
     * @param  array  $config
     * @return \GuzzleHttp\Client
     */
    protected function guzzle(array $config): HttpClient
    {
        return new HttpClient($config);
    }
}
