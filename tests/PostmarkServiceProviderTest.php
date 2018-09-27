<?php

namespace Coconuts\Mail;

class PostmarkServiceProviderTest extends TestCase
{
    /** @test */
    public function will_skip_registering_transport_if_not_using_postmark()
    {
        $this->app['config']->set('mail.driver', 'array');

        (new PostmarkServiceProvider($this->app))->boot();

        $this->assertEmpty($this->readProperty($this->app['swift.transport'], 'customCreators'));
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [];
    }
}
