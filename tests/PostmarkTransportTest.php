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

        $message = new \Swift_Message('Foo subject', 'Bar body');
        $message->setFrom('myself@example.com');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setReplyTo('replyTo@example.com');
        $headers = $message->getHeaders();
        $headers->addTextHeader('Tag', 'Tagged');
        $this->message = $message;

        $client = new Client();
        $key = $this->app['config']->get('services.postmark.secret');
        $this->transport = new PostmarkTransport($client, $key);
    }

    /** @test */
    public function can_given_contacts_into_a_comma_separated_string()
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
    public function can_get_the_from_field_as_a_string()
    {
        $string = $this->invokeMethod($this->transport, 'getFrom', [$this->message]);

        $this->assertEquals('myself@example.com', $string);
        $this->assertNotEquals('me@example.com', $string);
    }

    /** @test */
    public function can_create_the_proper_payload_for_a_message()
    {
        $payload = $this->invokeMethod($this->transport, 'payload', [$this->message]);

        $this->assertArrayHasKey('headers', $payload);
        $this->assertArrayHasKey('Accept', $payload['headers']);
        $this->assertArrayHasKey('X-Postmark-Server-Token', $payload['headers']);
        $this->assertArrayHasKey('json', $payload);
        $this->assertArrayHasKey('From', $payload['json']);
        $this->assertArrayHasKey('To', $payload['json']);
        $this->assertArrayHasKey('Subject', $payload['json']);
        $this->assertArrayHasKey('Tag', $payload['json']);
        $this->assertArrayHasKey('HtmlBody', $payload['json']);
    }

    /** @test */
    public function can_send_an_email_through_postmarks_api()
    {
        try {
            $this->transport->send($this->message);
        } catch (RequestException $e) {
            $this->fail($e->getMessage());
        }
    }
}
