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

        $this->app['swift.transport']->extend('postmark', function ($app) use ($config) {
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
        return new HttpClient(Arr::add(
            Arr::get($config, 'guzzle', []),
            'connect_timeout',
            60
        ));
    }
}
