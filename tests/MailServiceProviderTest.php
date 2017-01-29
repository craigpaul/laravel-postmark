<?php

namespace Coconuts\Mail;

use Illuminate\Mail\TransportManager;
use Illuminate\Mail\MailServiceProvider as LaravelMailServiceProvider;

class MailServiceProviderTest extends TestCase
{
    /** @test */
    public function extends_existing_mail_provider_to_maintain_other_functionality()
    {
        $provider = new MailServiceProvider($this->app);

        $this->assertTrue(is_subclass_of($provider, LaravelMailServiceProvider::class));
    }

    /** @test */
    public function registers_own_transport_manager_which_extends_existing_to_maintain_other_functionality()
    {
        $provider = new MailServiceProvider($this->app);

        $provider->register();

        $manager = $this->app['swift.transport'];
        $this->assertTrue(is_subclass_of($manager, TransportManager::class));
    }
}
