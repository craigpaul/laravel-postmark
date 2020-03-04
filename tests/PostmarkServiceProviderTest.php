<?php

namespace Coconuts\Mail\Tests;

use Coconuts\Mail\PostmarkServiceProvider;

class PostmarkServiceProviderTest extends TestCase
{
    /** @test */
    public function will_skip_registering_the_postmark_driver_if_not_using_postmark_on_laravel_v6_and_lower()
    {
        if (! $this->app->has('swift.transport')) {
            $this->markTestSkipped('swift.transport is only available for Laravel 6.0 and lower.');
        }

        $this->app['config']->set('mail.driver', 'array');

        tap(new PostmarkServiceProvider($this->app), function ($provider) {
            $this->assertFalse($this->invokeMethod($provider, 'shouldRegisterPostmarkDriver'));
            $provider->boot();
        });

        $this->assertEmpty(
            $this->readProperty($this->app['swift.transport'], 'customCreators')
        );
    }

    /** @test */
    public function will_always_register_the_postmark_driver_on_laravel_v7_or_higher()
    {
        if (! $this->app->has('mail.manager')) {
            $this->markTestSkipped('mail.manager is only available on Laravel 7.0 or higher.');
        }

        tap(new PostmarkServiceProvider($this->app), function ($provider) {
            $this->assertTrue($this->invokeMethod($provider, 'shouldRegisterPostmarkDriver'));
            $provider->boot();
        });

        tap($this->readProperty($this->app['mail.manager'], 'customCreators'), function ($customCreators) {
            $this->assertNotEmpty($customCreators);
            $this->assertArrayHasKey('postmark', $customCreators);
        });
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
