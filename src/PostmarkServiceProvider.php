<?php

namespace Coconuts\Mail;

use function array_merge;
use function config;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

class PostmarkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishing();

        $this->mergeConfigFrom(__DIR__.'/../config/postmark.php', 'postmark');

        $this->registerPostmarkDriver();
    }

    private function registerPostmarkDriver(): void
    {
        if (! $this->shouldRegisterPostmarkDriver()) {
            return;
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'postmark');

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

    protected function shouldRegisterPostmarkDriver(): bool
    {
        if ($this->app->has('mail.manager')) {
            return true;
        }

        return $this->app['config']['mail.driver'] === 'postmark';
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/postmark.php' => config_path('postmark.php'),
            ], 'postmark-config');
        }
    }

    protected function guzzle(array $config): HttpClient
    {
        return new HttpClient(array_merge($config, [
            'base_uri' => empty($config['base_uri'])
                ? 'https://api.postmarkapp.com'
                : $config['base_uri']
        ]));
    }
}
