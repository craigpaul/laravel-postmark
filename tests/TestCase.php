<?php

namespace CraigPaul\Mail\Tests;

use CraigPaul\Mail\PostmarkServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mail.default', 'postmark');
        $app['config']->set('mail.mailers.postmark.message_stream_id', 'MESSAGE_STREAM_ID');
        $app['config']->set('services.postmark.token', 'POSTMARK_API_TEST');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [PostmarkServiceProvider::class];
    }
}
