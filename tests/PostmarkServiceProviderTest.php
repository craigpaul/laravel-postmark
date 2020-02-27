<?php

namespace Coconuts\Mail\Tests;

use Coconuts\Mail\PostmarkServiceProvider;

class PostmarkServiceProviderTest extends TestCase
{
    /** @test */
    public function will_skip_registering_transport_if_not_using_postmark_on_laravel_v6_and_lower()
    {
        if (! $this->app->has('swift.transport')) {
            $this->markTestSkipped('swift.transport is only available for Laravel 6.0 and lower.');
        }

        $this->app['config']->set('mail.driver', 'array');

        (new PostmarkServiceProvider($this->app))->boot();

        $this->assertEmpty($this->readProperty($this->app['swift.transport'], 'customCreators'));
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [];
    }
}
