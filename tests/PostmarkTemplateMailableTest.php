<?php

namespace Coconuts\Mail\Tests;

use Illuminate\Support\Facades\Mail;
use Coconuts\Mail\PostmarkTemplateMailable;

class PostmarkTemplateMailableTest extends TestCase
{
    /** @var \Coconuts\Mail\MailMessage */
    protected $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = new PostmarkTemplateMailable;
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

    /** @test */
    public function can_send_an_email()
    {
        Mail::fake();

        Mail::to('mail@example.com')
            ->send((new PostmarkTemplateMailable())
                ->identifier(8675309)
                ->include([
                    'name' => 'Customer Name',
                    'action_url' => 'https://example.com/login',
                ])
            );
        
        Mail::assertSent(PostmarkTemplateMailable::class);

        Mail::to('mail@example.com')
            ->queue((new PostmarkTemplateMailable())
                ->alias('test-with-alias')
                ->include([
                    'name' => 'Customer Name',
                    'action_url' => 'https://example.com/login',
                ])
            );
    
        Mail::assertQueued(PostmarkTemplateMailable::class);
    }

    /** @test */
    public function has_correct_mail_content()
    {
        $mailable = (new PostmarkTemplateMailable())
            ->alias('test-with-alias')
            ->include([
                'random' => 'data'
            ]);

        $mailable->assertSeeInHtml('"alias":"test-with-alias"');
        $mailable->assertSeeInHtml('"model":{"random":"data"}');
    }
}
