<?php

namespace Coconuts\Mail\Tests;

use Coconuts\Mail\PostmarkTransport;

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
