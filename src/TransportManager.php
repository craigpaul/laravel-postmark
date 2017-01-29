<?php

namespace Coconuts\Mail;

use Illuminate\Mail\TransportManager as LaravelTransportManager;

class TransportManager extends LaravelTransportManager
{
    /**
     * Create an instance of the Postmark Swift Transport driver.
     */
    public function createPostmarkDriver()
    {
        $config = $this->app['config']->get('services.postmark', []);

        return new PostmarkTransport(
            $this->guzzle($config),
            $config['secret']
        );
    }
}
