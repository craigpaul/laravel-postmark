<?php

namespace CraigPaul\Mail\Tests;

use CraigPaul\Mail\PostmarkTransport;
use CraigPaul\Mail\Tests\Factories\Email;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\Header\MetadataHeader;
use function explode;
use function basename;
use const DATE_RFC3339_EXTENDED;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
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

        $factory->assertSent(function (Request $request) use ($contentId, $email) {
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
        $message->getHeaders()->addTextHeader($header->getKey(), $header->getValue());

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = $this->fakeSuccessfulEmail($email);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($email->getMessageId(), $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($email) {
            $header = $email->getHeader();
            $metadata = $email->getMetadata();

            return $request['MessageStream'] === $this->getMessageStreamId()
                && $request['Tag'] === $email->getTag()
                && $request['Metadata'] === [$metadata->getKey() => $metadata->getValue()]
                && $request['Headers'] === [['Name' => $header->getKey(), 'Value' => $header->getValue()]];
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
