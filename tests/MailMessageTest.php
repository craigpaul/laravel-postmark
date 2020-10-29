<?php

namespace CraigPaul\Mail\Tests;

use CraigPaul\Mail\MailMessage;

class MailMessageTest extends TestCase
{
    protected MailMessage $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = new MailMessage;
    }

    /** @test */
    public function can_set_the_template_identifier()
    {
        $message = $this->message->identifier(12345);

        $data = $message->data();

        $this->assertArrayHasKey('id', $data);
        $this->assertSame(12345, $data['id']);
    }

    /** @test */
    public function can_set_the_template_alias()
    {
        $message = $this->message->alias('aliased-template');

        $data = $message->data();

        $this->assertArrayHasKey('alias', $data);
        $this->assertSame('aliased-template', $data['alias']);
    }

    /** @test */
    public function can_set_the_template_model()
    {
        $message = $this->message->include([
            'random' => 'data',
        ]);

        $data = $message->data();

        $this->assertArrayHasKey('model', $data);
        $this->assertSame([
            'random' => 'data',
        ], $data['model']);
    }
}
