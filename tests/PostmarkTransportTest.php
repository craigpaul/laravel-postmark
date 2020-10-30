<?php

namespace CraigPaul\Mail\Tests;

use CraigPaul\Mail\Exceptions\PostmarkException;
use CraigPaul\Mail\PostmarkTransport;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function json_encode;
use Swift_Attachment;
use Swift_Message;
use function tap;

class PostmarkTransportTest extends TestCase
{
    protected Swift_Message $message;

    protected PostmarkTransport $transport;

    public function setUp(): void
    {
        parent::setUp();

        $attachment = new Swift_Attachment('test attachment', 'test.txt');
        $attachment->setContentType('text/plain');

        $this->message = tap(new Swift_Message, function ($message) use ($attachment) {
            $message->setSubject('Foo subject');
            $message->setBody('Bar body', 'text/plain');
            $message->setFrom('myself@example.com');
            $message->setTo('me@example.com');
            $message->setCc('cc@example.com');
            $message->setBcc('bcc@example.com');
            $message->setReplyTo('replyTo@example.com');
            $message->attach($attachment);
            $message->getHeaders()->addTextHeader('Tag', 'Tagged');
            $message->getHeaders()->addTextHeader('Custom-Header', 'Custom-Value');
            $message->getHeaders()->addTextHeader('metadata-metadata', 'metadata');
            $message->getHeaders()->addTextHeader('metadata-other-data', 'some other data');
            $message->addPart('<html>', 'text/html');
        });

        $this->transport = app('mailer')->getSwiftMailer()->getTransport();
    }

    private function getPayload(Swift_Message $message): array
    {
        return $this->invokeMethod($this->transport, 'payload', [$message]);
    }

    /** @test */
    public function can_get_the_api_endpoint()
    {
        $endpoint = $this->invokeMethod($this->transport, 'getApiEndpoint', [$this->message]);

        $this->assertSame('https://api.postmarkapp.com/email', $endpoint);
    }

    /** @test */
    public function can_get_given_contacts_into_a_comma_separated_string()
    {
        $to = $this->invokeMethod($this->transport, 'getContacts', [['me@example.com' => '']]);

        $this->assertEquals('me@example.com', $to);

        $multiple = $this->invokeMethod($this->transport, 'getContacts', [[
            'john@example.com' => 'John',
            'jane@example.com' => 'Jane',
            'user@example.com' => 'User',
        ]]);

        $this->assertEquals('John <john@example.com>,Jane <jane@example.com>,User <user@example.com>', $multiple);
    }

    /** @test */
    public function can_get_given_body()
    {
        $body = $this->invokeMethod($this->transport, 'getBody', [$this->message]);

        $this->assertSame('Bar body', $body);
    }

    /** @test */
    public function get_body_returns_empty_string_when_there_is_no_body_set()
    {
        $body = $this->invokeMethod($this->transport, 'getBody', [new Swift_Message]);

        $this->assertSame('', $body);
    }

    /** @test */
    public function can_get_a_mime_part_from_message()
    {
        $this->message->addPart('<html>', 'text/html');

        $part = $this->invokeMethod($this->transport, 'getMimePart', [$this->message, 'text/html']);

        $this->assertSame('<html>', $part->getBody());
    }

    /** @test */
    public function can_get_given_subject()
    {
        $subject = $this->invokeMethod($this->transport, 'getSubject', [$this->message]);

        $this->assertSame('Foo subject', $subject);
    }

    /** @test */
    public function get_subject_returns_empty_string_when_there_is_no_subject_set()
    {
        $subject = $this->invokeMethod($this->transport, 'getSubject', [new Swift_Message]);

        $this->assertSame('', $subject);
    }

    /** @test */
    public function can_get_given_tag()
    {
        $tag = $this->invokeMethod($this->transport, 'getTag', [$this->message]);

        $this->assertSame('Tagged', $tag);
    }

    /** @test */
    public function get_tag_returns_empty_string_when_there_is_no_tag_present()
    {
        $tag = $this->invokeMethod($this->transport, 'getTag', [new Swift_Message]);

        $this->assertSame('', $tag);
    }

    /** @test */
    public function get_tag_returns_only_the_last_tag_when_multiple_tags_were_set()
    {
        $this->message->getHeaders()->addTextHeader('Tag', 'TestTag1');
        $this->message->getHeaders()->addTextHeader('Tag', 'TestTag2');

        $tag = $this->invokeMethod($this->transport, 'getTag', [$this->message]);

        $this->assertSame('TestTag2', $tag);
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
            ],
        ], $attachments);
    }

    /** @test */
    public function can_get_given_headers_into_array()
    {
        $headers = $this->invokeMethod($this->transport, 'getHeaders', [$this->message]);

        $this->assertEquals([
            [
                'Name' => 'Custom-Header',
                'Value' => 'Custom-Value',
            ],
        ], $headers);
    }

    /** @test */
    public function can_create_the_proper_payload_structure_for_a_message()
    {
        $payload = $this->getPayload($this->message);

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
            $this->assertArrayNotHasKey('TextBody', $json);
            $this->assertArrayHasKey('ReplyTo', $json);
            $this->assertArrayHasKey('Attachments', $json);
        });
    }

    /** @test */
    public function can_create_the_proper_payload_for_templated_message()
    {
        $message = tap($this->message, function (Swift_Message $message) {
            $body = [
                'id' => 12345,
                'alias' => 'aliased-template',
                'model' => [
                    'random' => 'data',
                ],
            ];

            $message->setBody(json_encode($body), 'text/plain');
        });

        $payload = $this->getPayload($message);

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
            $this->assertArrayHasKey('Tag', $json);
            $this->assertArrayHasKey('ReplyTo', $json);
            $this->assertArrayHasKey('Attachments', $json);

            $this->assertArrayHasKey('TemplateId', $json);
            $this->assertArrayHasKey('TemplateAlias', $json);
            $this->assertArrayHasKey('TemplateModel', $json);
        });
    }

    /** @test */
    public function payload_has_the_proper_header_values()
    {
        $this->transport = new PostmarkTransport(
            new Client(),
            'super-secret-token'
        );

        $payload = $this->getPayload($this->message);

        tap($payload['headers'], function ($headers) {
            $this->assertSame('application/json', $headers['Content-Type']);
            $this->assertSame('application/json', $headers['Accept']);
            $this->assertSame('super-secret-token', $headers['X-Postmark-Server-Token']);
        });
    }

    /** @test */
    public function payload_has_the_proper_from_address()
    {
        $this->message->setFrom('john@example.com', 'John Doe');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('John Doe <john@example.com>', $json['From']);
        });
    }

    /** @test */
    public function payload_has_the_proper_to_address()
    {
        $this->message->setTo('jane@example.com', 'Jane Doe');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('Jane Doe <jane@example.com>', $json['To']);
        });
    }

    /** @test */
    public function payload_has_the_proper_cc_address()
    {
        $this->message->setCc('foo@example.com', 'Foo');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('Foo <foo@example.com>', $json['Cc']);
        });
    }

    /** @test */
    public function payload_has_the_proper_bcc_address()
    {
        $this->message->setBcc('bar@example.com', 'Bar');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('Bar <bar@example.com>', $json['Bcc']);
        });
    }

    /** @test */
    public function payload_has_the_proper_subject()
    {
        $this->message->setSubject('Lorem ipsum.');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('Lorem ipsum.', $json['Subject']);
        });
    }

    /** @test */
    public function payload_has_the_proper_tag()
    {
        $this->message->getHeaders()->addTextHeader('Tag', 'TestTag');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('TestTag', $json['Tag']);
        });
    }

    /** @test */
    public function payload_has_the_proper_html_body()
    {
        $this->message->setBody('<html>', 'text/html');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('<html>', $json['HtmlBody']);
            $this->assertArrayNotHasKey('TextBody', $json);
        });
    }

    /** @test */
    public function payload_has_the_proper_text_body()
    {
        $message = new Swift_Message;
        $message->setBody('Lorem ipsum.', 'text/plain');

        $payload = $this->getPayload($message);

        tap($payload['json'], function ($json) {
            $this->assertSame('Lorem ipsum.', $json['TextBody']);
            $this->assertArrayNotHasKey('HtmlBody', $json);
        });
    }

    /** @test */
    public function payload_has_the_proper_html_and_text_body()
    {
        $this->message->setBody('Lorem ipsum.', 'text/plain');
        $this->message->addPart('<html>', 'text/html');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('<html>', $json['HtmlBody']);
            $this->assertFalse(isset($json['TextBody']));
        });
    }

    /** @test */
    public function payload_has_the_proper_html_and_text_body_reverse_order()
    {
        $message = new Swift_Message;
        $message->setBody('<html>', 'text/html');
        $message->addPart('Lorem ipsum.', 'text/plain');

        $payload = $this->getPayload($message);

        tap($payload['json'], function ($json) {
            $this->assertSame('Lorem ipsum.', $json['TextBody']);
            $this->assertSame('<html>', $json['HtmlBody']);
        });
    }

    /** @test */
    public function payload_has_the_proper_reply_to_address()
    {
        $this->message->setReplyTo('replyTo@example.com', 'ReplyName');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('ReplyName <replyTo@example.com>', $json['ReplyTo']);
        });
    }

    /** @test */
    public function payload_has_the_proper_attachments()
    {
        $attachment1 = new Swift_Attachment('test attachment 1', 'attachment1.txt');
        $attachment1->setContentType('text/plain');

        $attachment2 = new Swift_Attachment('test attachment 2', 'attachment2.txt');
        $attachment2->setContentType('text/plain');

        $message = new Swift_Message;
        $message->attach($attachment1);
        $message->attach($attachment2);

        $payload = $this->getPayload($message);

        tap($payload['json']['Attachments'], function ($json) {
            $this->assertCount(2, $json);
            $this->assertContains([
                'Name' => 'attachment1.txt',
                'Content' => 'dGVzdCBhdHRhY2htZW50IDE=',
                'ContentType' => 'text/plain',
            ], $json);
            $this->assertContains([
                'Name' => 'attachment2.txt',
                'Content' => 'dGVzdCBhdHRhY2htZW50IDI=',
                'ContentType' => 'text/plain',
            ], $json);
        });
    }

    /** @test */
    public function empty_fields_are_not_present_in_the_json_payload()
    {
        $message = new Swift_Message;

        $payload = $this->getPayload($message);

        tap($payload['json'], function ($json) {
            $this->assertArrayNotHasKey('Cc', $json);
            $this->assertArrayNotHasKey('Bcc', $json);
            $this->assertArrayNotHasKey('Tag', $json);
            $this->assertArrayNotHasKey('ReplyTo', $json);
            $this->assertArrayNotHasKey('Attachments', $json);
        });
    }

    /** @test */
    public function required_fields_are_present_in_the_json_payload()
    {
        $message = new Swift_Message;

        $payload = $this->getPayload($message);

        tap($payload['json'], function ($json) {
            $this->assertArrayHasKey('From', $json);
            $this->assertArrayHasKey('To', $json);
            $this->assertArrayHasKey('TextBody', $json);
        });
    }

    /** @test */
    public function display_name_with_a_comma_should_be_double_quoted_in_the_json_payload()
    {
        $this->message->setTo('john@example.com', 'Doe, John');

        $payload = $this->getPayload($this->message);

        tap($payload['json'], function ($json) {
            $this->assertSame('"Doe, John" <john@example.com>', $json['To']);
        });
    }

    /** @test */
    public function can_send_an_email_through_postmarks_api()
    {
        try {
            $this->transport->send($this->message);
            $this->assertNotNull($this->message->getHeaders()->get('X-Message-ID'));
        } catch (RequestException $e) {
            $this->fail($e->getMessage());
        }
    }

    /** @test */
    public function creating_a_new_instance_of_postmark_transport_without_setting_a_postmark_secret_throws_an_exception()
    {
        $this->expectException(PostmarkException::class);
        $this->expectExceptionMessage('The Postmark secret is not set. Make sure that the `postmark.secret` config key is set.');

        $this->transport = new PostmarkTransport(
            new Client(),
            null
        );

        $this->transport->send($this->message);
    }

    /** @test */
    public function can_get_metadata()
    {
        $metadata = $this->invokeMethod($this->transport, 'getMetadata', [$this->message]);

        $this->assertSame([
            'metadata' => 'metadata',
            'other-data' => 'some other data',
        ], $metadata);
    }

    /** @test */
    public function metadata_is_empty_array_when_not_set()
    {
        $metadata = $this->invokeMethod($this->transport, 'getMetadata', [new Swift_Message]);

        $this->assertSame([], $metadata);
    }

    /** @test */
    public function metadata_can_be_non_ascii()
    {
        $message = new Swift_Message;
        $message->getHeaders()->addTextHeader('metadata-other-data¹', 'some other data¹');

        $metadata = $this->invokeMethod($this->transport, 'getMetadata', [$message]);

        $this->assertSame([
            'other-data¹' => 'some other data¹',
        ], $metadata);
    }
}
