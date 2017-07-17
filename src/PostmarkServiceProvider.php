<?php

namespace Coconuts\Mail;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class PostmarkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $config = $this->app['config']->get('services.postmark', []);

        $this->app['swift.transport']->extend('postmark', function () use ($config) {
            return new PostmarkTransport(
                $this->guzzle($config),
                $config['secret']
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
        return new HttpClient(array_add(
            array_get($config, 'guzzle', []),
            'connect_timeout',
            60
        ));
    }
}
