<?php

namespace Coconuts\Mail;

class TransportManagerTest extends TestCase
{
    /** @test */
    public function can_create_postmark_driver()
    {
        $manager = new TransportManager($this->app);

        $transport = $manager->createPostmarkDriver();

        $this->assertEquals(PostmarkTransport::class, get_class($transport));
    }
}
