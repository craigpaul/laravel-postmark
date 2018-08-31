<?php

namespace Coconuts\Mail;

class TransportManagerTest extends TestCase
{
    /** @test */
    public function can_get_postmark_driver()
    {
        $manager = $this->app['swift.transport'];

        $transport = $manager->driver('postmark');

        $this->assertInstanceOf(PostmarkTransport::class, $transport);
    }
}
