<?php

namespace Coconuts\Mail\Tests;

use Coconuts\Mail\PostmarkTransport;

class TransportManagerTest extends TestCase
{
    /** @test */
    public function can_get_postmark_driver()
    {
        $this->assertInstanceOf(
            PostmarkTransport::class,
            app('mailer')->getSwiftMailer()->getTransport()
        );
    }
}
