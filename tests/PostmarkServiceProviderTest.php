<?php

namespace Coconuts\Mail\Tests;

use Coconuts\Mail\PostmarkServiceProvider;
use function tap;

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

    /** @test */
    public function will_register_http_client_with_temporary_base_url_depending_on_environment_setup()
    {
        if (! $this->app->has('swift.transport')) {
            $this->markTestSkipped('swift.transport is only available for Laravel 6.0 and lower.');
        }

        $this->app['config']->set('postmark.validating.tls', true);

        tap(new PostmarkServiceProvider($this->app), function ($provider) {
            $provider->boot();
        });

        $driver = $this->app['swift.transport']->driver('postmark');

        tap($this->readProperty($driver, 'client'), function ($client) {
            $this->assertSame(
                'api-ssl-temp.postmarkapp.com',
                $client->getConfig('base_uri')->getHost()
            );
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
