<?php

namespace CraigPaul\Mail\Tests;

use function basename;
use CraigPaul\Mail\PostmarkServerTokenHeader;
use CraigPaul\Mail\PostmarkTransport;
use CraigPaul\Mail\PostmarkTransportException;
use CraigPaul\Mail\TemplatedMailable;
use CraigPaul\Mail\Tests\Factories\Email;
use CraigPaul\Mail\Tests\Factories\Template;
use CraigPaul\Mail\Tests\Notifications\TemplatedNotification;
use const DATE_RFC3339_EXTENDED;
use function explode;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email as SymfonyEmail;

class PostmarkTransportTest extends TestCase
{
    public function testTransportSendsBasicMessageSuccessfully()
    {
        $email = Email::createBasic();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getToAddress(), $email->getToName())
            ->from($email->getFrom())
            ->replyTo($email->getReplyTo())
            ->html($email->getHtmlBody())
            ->text($email->getTextBody());

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = $this->fakeSuccessfulEmail($email);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($email->getMessageId(), $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($email) {
            return $request->method() === 'POST'
                && $request->isJson()
                && $request->hasHeader('X-Postmark-Server-Token', $this->getToken())
                && $request->url() === 'https://api.postmarkapp.com/email'
                && $request['From'] === $email->getFrom()
                && $request['To'] === '"'.$email->getToName().'" <'.$email->getToAddress().'>'
                && $request['Subject'] === $email->getSubject()
                && $request['HtmlBody'] === $email->getHtmlBody()
                && $request['TextBody'] === $email->getTextBody()
                && $request['ReplyTo'] === $email->getReplyTo();
        });
    }

    public function testTransportSendsMessageWithCarbonCopiesSuccessfully()
    {
        $email = Email::createCopies();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getToAddress(), $email->getToName())
            ->cc($email->getCc())
            ->bcc($email->getBcc())
            ->from($email->getFrom())
            ->html($email->getHtmlBody())
            ->text($email->getTextBody());

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = $this->fakeSuccessfulEmail($email);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($email->getMessageId(), $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($email) {
            return $request['To'] === '"'.$email->getToName().'" <'.$email->getToAddress().'>'
                && $request['Cc'] === $email->getCc()
                && $request['Bcc'] === $email->getBcc();
        });
    }

    public function testTransportSendsMessageWithAttachmentSuccessfully()
    {
        $email = Email::createAttachment();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getToAddress(), $email->getToName())
            ->from($email->getFrom())
            ->html($email->getHtmlBody())
            ->text($email->getTextBody())
            ->attach($email->getAttachment());

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = $this->fakeSuccessfulEmail($email);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($email->getMessageId(), $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($email) {
            $attachment = $request['Attachments'][0];

            return $attachment['Name'] === basename($email->getAttachment())
                && ! empty($attachment['Content'])
                && $attachment['ContentType'] === 'image/png'
                && empty($attachment['ContentID']);
        });
    }

    public function testTransportSendsMessageWithEmbeddedAttachmentSuccessfully()
    {
        $email = Email::createAttachment();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getToAddress(), $email->getToName())
            ->from($email->getFrom())
            ->html($email->getHtmlBody())
            ->text($email->getTextBody());

        $contentId = $message->embed($email->getAttachment());

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = $this->fakeSuccessfulEmail($email);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($email->getMessageId(), $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($contentId) {
            $attachment = $request['Attachments'][0];
            [, $name] = explode(':', $contentId);

            return $attachment['Name'] === $name
                && ! empty($attachment['Content'])
                && $attachment['ContentType'] === 'image/png'
                && ! empty($attachment['ContentID'])
                && $attachment['ContentID'] === $contentId;
        });
    }

    public function testTransportSendsMessageWithCustomizationsSuccessfully()
    {
        $email = Email::createCustomizations();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getToAddress(), $email->getToName())
            ->from($email->getFrom())
            ->html($email->getHtmlBody())
            ->text($email->getTextBody());

        $header = $email->getHeader();
        $metadata = $email->getMetadata();

        $message->getHeaders()->add(new TagHeader($email->getTag()));
        $message->getHeaders()->add(new MetadataHeader($metadata->getKey(), $metadata->getValue()));
        $message->getHeaders()->add(new PostmarkServerTokenHeader($metadata->getValue()));
        $message->getHeaders()->addTextHeader($header->getKey(), $header->getValue());

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = $this->fakeSuccessfulEmail($email);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($email->getMessageId(), $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($email) {
            $header = $email->getHeader();
            $metadata = $email->getMetadata();

            return $request->hasHeader('X-Postmark-Server-Token', $metadata->getValue())
                && $request['MessageStream'] === $this->getMessageStreamId()
                && $request['Tag'] === $email->getTag()
                && $request['Metadata'] === [$metadata->getKey() => $metadata->getValue()]
                && $request['Headers'] === [['Name' => $header->getKey(), 'Value' => $header->getValue()]];
        });
    }

    public function testTransportHandlesErrorsWhileAttemptingToSendMessage()
    {
        $email = Email::createBasic();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getToAddress(), $email->getToName())
            ->from($email->getFrom())
            ->replyTo($email->getReplyTo())
            ->html($email->getHtmlBody())
            ->text($email->getTextBody());

        $symfonyMessage = $message->getSymfonyMessage();

        $this->fakeFailureEmail(402, 'Invalid JSON');

        $this->expectException(PostmarkTransportException::class);
        $this->expectExceptionCode(402);
        $this->expectExceptionMessage('Invalid JSON');

        $this->sendMessage($symfonyMessage);
    }

    public function testCanSendTemplatedMailableSuccessfullyUsingAnAlias()
    {
        $template = Template::create();

        $factory = $this->fakeSuccessfulTemplate($template);

        $mailable = (new TemplatedMailable())
            ->alias($template->getAlias())
            ->include($template->getModel());

        Mail::to($template->getToAddress())->send($mailable);

        $factory->assertSent(function (Request $request) use ($template) {
            return $request->method() === 'POST'
                && $request->isJson()
                && $request->hasHeader('X-Postmark-Server-Token', $this->getToken())
                && $request->url() === 'https://api.postmarkapp.com/email/withTemplate'
                && $request['From'] === '"'.$template->getFromName().'" <'.$template->getFromAddress().'>'
                && $request['To'] === $template->getToAddress()
                && empty($request['TemplateId'])
                && $request['TemplateModel'] === $template->getModel();
        });
    }

    public function testCanSendTemplatedMailableSuccessfullyUsingAnIdentifier()
    {
        $template = Template::create();

        $factory = $this->fakeSuccessfulTemplate($template);

        $mailable = (new TemplatedMailable())
            ->identifier($template->getId())
            ->include($template->getModel());

        Mail::to($template->getToAddress())->send($mailable);

        $factory->assertSent(function (Request $request) use ($template) {
            return $request->method() === 'POST'
                && $request->isJson()
                && $request->hasHeader('X-Postmark-Server-Token', $this->getToken())
                && $request->url() === 'https://api.postmarkapp.com/email/withTemplate'
                && $request['From'] === '"'.$template->getFromName().'" <'.$template->getFromAddress().'>'
                && $request['To'] === $template->getToAddress()
                && $request['TemplateId'] === $template->getId()
                && empty($request['TemplateAlias'])
                && $request['TemplateModel'] === $template->getModel();
        });
    }

    public function testCanSendTemplatedMailMessageSuccessfullyUsingAlias()
    {
        $template = Template::create();

        $factory = $this->fakeSuccessfulTemplate($template);

        Notification::route('mail', $template->getToAddress())->notify(new TemplatedNotification($template, 'alias'));

        $factory->assertSent(function (Request $request) use ($template) {
            return $request->method() === 'POST'
                && $request->isJson()
                && $request->hasHeader('X-Postmark-Server-Token', $this->getToken())
                && $request->url() === 'https://api.postmarkapp.com/email/withTemplate'
                && $request['From'] === '"'.$template->getFromName().'" <'.$template->getFromAddress().'>'
                && $request['To'] === $template->getToAddress()
                && empty($request['TemplateId'])
                && $request['TemplateAlias'] === $template->getAlias()
                && $request['TemplateModel'] === $template->getModel();
        });
    }

    public function testCanSendTemplatedMailMessageSuccessfullyUsingIdentifier()
    {
        $template = Template::create();

        $factory = $this->fakeSuccessfulTemplate($template);

        Notification::route('mail', $template->getToAddress())->notify(new TemplatedNotification($template, 'identifier'));

        $factory->assertSent(function (Request $request) use ($template) {
            return $request->method() === 'POST'
                && $request->isJson()
                && $request->hasHeader('X-Postmark-Server-Token', $this->getToken())
                && $request->url() === 'https://api.postmarkapp.com/email/withTemplate'
                && $request['From'] === '"'.$template->getFromName().'" <'.$template->getFromAddress().'>'
                && $request['To'] === $template->getToAddress()
                && $request['TemplateId'] === $template->getId()
                && empty($request['TemplateAlias'])
                && $request['TemplateModel'] === $template->getModel();
        });
    }

    protected function getMessageStreamId(): ?string
    {
        return $this->app['config']->get('mail.mailers.postmark.message_stream_id');
    }

    protected function getToken(): string
    {
        return $this->app['config']->get('services.postmark.token');
    }

    protected function fakeFailureEmail(int $error, string $message): Factory
    {
        $factory = Http::fake([
            'https://api.postmarkapp.com/email' => Http::response([
                'ErrorCode' => $error,
                'Message' => $message,
            ], Response::HTTP_UNPROCESSABLE_ENTITY),
        ]);

        $this->instance(Factory::class, $factory);

        return $factory;
    }

    protected function fakeSuccessfulEmail(Email $email): Factory
    {
        $factory = Http::fake([
            'https://api.postmarkapp.com/email' => Http::response([
                'To' => $email->getToAddress(),
                'SubmittedAt' => Date::now()->format(DATE_RFC3339_EXTENDED),
                'MessageID' => $email->getMessageId(),
                'ErrorCode' => 0,
                'Message' => 'OK',
            ]),
        ]);

        $this->instance(Factory::class, $factory);

        return $factory;
    }

    protected function fakeSuccessfulTemplate(Template $template): Factory
    {
        $factory = Http::fake([
            'https://api.postmarkapp.com/email/withTemplate' => Http::response([
                'To' => $template->getToAddress(),
                'SubmittedAt' => Date::now()->format(DATE_RFC3339_EXTENDED),
                'MessageID' => $template->getMessageId(),
                'ErrorCode' => 0,
                'Message' => 'OK',
            ]),
        ]);

        $this->instance(Factory::class, $factory);

        return $factory;
    }

    protected function newMessage(): Message
    {
        return new Message(new SymfonyEmail());
    }

    protected function sendMessage(SymfonyEmail $symfonyMessage): ?SentMessage
    {
        return $this->app->makeWith(PostmarkTransport::class, [
            'messageStreamId' => $this->getMessageStreamId(),
            'token' => $this->getToken(),
        ])->send($symfonyMessage, Envelope::create($symfonyMessage));
    }
}
