<?php

namespace Coconuts\Mail;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PostmarkTransportTest extends TestCase
{
    /**
     * @var \Swift_Message
     */
    protected $message;

    /**
     * @var \Coconuts\Mail\PostmarkTransport
     */
    protected $transport;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $attachment = new \Swift_Attachment('test attachment', 'test.txt');
        $attachment->setContentType('text/plain');

        $this->message = tap(new \Swift_Message, function ($message) use ($attachment) {
            $message->setSubject('Foo subject');
            $message->setBody('Bar body', 'text/plain');
            $message->setFrom('myself@example.com');
            $message->setTo('me@example.com');
            $message->setCc('cc@example.com');
            $message->setBcc('bcc@example.com');
            $message->setReplyTo('replyTo@example.com');
            $message->attach($attachment);
            $message->getHeaders()->addTextHeader('Tag', 'Tagged');
        });

        $this->transport = new PostmarkTransport(
            new Client(),
            $this->app['config']->get('services.postmark.secret')
        );
    }

    /** @test */
    public function can_get_given_contacts_into_a_comma_separated_string()
    {
        $to = $this->invokeMethod($this->transport, 'getContacts', [$this->message->getTo()]);
        $cc = $this->invokeMethod($this->transport, 'getContacts', [$this->message->getCc()]);
        $bcc = $this->invokeMethod($this->transport, 'getContacts', [$this->message->getBcc()]);
        $replyTo = $this->invokeMethod($this->transport, 'getContacts', [$this->message->getReplyTo()]);

        $this->assertEquals('me@example.com', $to);
        $this->assertEquals('cc@example.com', $cc);
        $this->assertEquals('bcc@example.com', $bcc);
        $this->assertEquals('replyTo@example.com', $replyTo);
    }

    /** @test */
    public function can_get_given_attachments_into_array()
    {
        $attachments = $this->invokeMethod($this->transport, 'getAttachments', [$this->message]);

        $this->assertEquals([
            [
                'Name' => 'test.txt',
                'Content' => 'dGVzdCBhdHRhY2htZW50',
                'ContentType' => 'text/plain',
            ]
        ], $attachments);
    }

    /** @test */
    public function can_create_the_proper_payload_for_a_message()
    {
        $payload = $this->invokeMethod($this->transport, 'payload', [$this->message]);

        $this->assertArrayHasKey('headers', $payload);
        $this->assertArrayHasKey('json', $payload);

        tap($payload['headers'], function ($headers) {
            $this->assertArrayHasKey('Content-Type', $headers);
            $this->assertArrayHasKey('Accept', $headers);
            $this->assertArrayHasKey('X-Postmark-Server-Token', $headers);
        });

        tap($payload['json'], function ($json) {
            $this->assertArrayHasKey('From', $json);
            $this->assertArrayHasKey('To', $json);
            $this->assertArrayHasKey('Cc', $json);
            $this->assertArrayHasKey('Bcc', $json);
            $this->assertArrayHasKey('Subject', $json);
            $this->assertArrayHasKey('Tag', $json);
            $this->assertArrayHasKey('HtmlBody', $json);
            $this->assertArrayHasKey('ReplyTo', $json);
            $this->assertArrayHasKey('Attachments', $json);
        });

        tap($payload['json']['Attachments'][0], function ($attachment) {
            $this->assertSame('test.txt', $attachment['Name']);
            $this->assertSame(base64_encode('test attachment'), $attachment['Content']);
            $this->assertSame('text/plain', $attachment['ContentType']);
        });
    }

    /** @test */
    public function can_send_an_email_through_postmarks_api()
    {
        try {
            $this->transport->send($this->message);
            $this->assertNotNull($this->message->getHeaders()->get('X-PM-Message-Id'));
        } catch (RequestException $e) {
            $this->fail($e->getMessage());
        }
    }
}
