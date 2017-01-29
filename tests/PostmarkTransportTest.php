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
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $this->message = $message;

        $client = new Client();
        $key = $this->app['config']->get('services.postmark.secret');
        $this->transport = new PostmarkTransport($client, $key);
    }

    /** @test */
    public function can_merge_all_contacts_into_a_single_array()
    {
        $contacts = $this->invokeMethod($this->transport, 'allContacts', [$this->message]);

        $this->assertArrayHasKey('me@example.com', $contacts);
        $this->assertArrayHasKey('cc@example.com', $contacts);
        $this->assertArrayHasKey('bcc@example.com', $contacts);
        $this->assertArrayNotHasKey('myself@example.com', $contacts);
    }

    /** @test */
    public function can_combine_all_contacts_into_a_comma_separated_string()
    {
        $string = $this->invokeMethod($this->transport, 'getTo', [$this->message]);

        $this->assertEquals('me@example.com,cc@example.com,bcc@example.com', $string);
        $this->assertNotEquals('cc@example.com,bcc@example.com,me@example.com', $string);
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
        $to = $this->invokeMethod($this->transport, 'getTo', [$this->message]);

        $payload = $this->invokeMethod($this->transport, 'payload', [$this->message, $to]);

        $this->assertArrayHasKey('headers', $payload);
        $this->assertArrayHasKey('Accept', $payload['headers']);
        $this->assertArrayHasKey('X-Postmark-Server-Token', $payload['headers']);
        $this->assertArrayHasKey('json', $payload);
        $this->assertArrayHasKey('From', $payload['json']);
        $this->assertArrayHasKey('To', $payload['json']);
        $this->assertArrayHasKey('Subject', $payload['json']);
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
