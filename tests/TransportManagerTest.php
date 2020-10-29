<?php

namespace CraigPaul\Mail\Tests;

use CraigPaul\Mail\PostmarkTransport;

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
