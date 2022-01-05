<?php

namespace CraigPaul\Mail\Tests;

use Illuminate\Mail\Message;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Date;
use CraigPaul\Mail\PostmarkTransport;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use CraigPaul\Mail\Tests\Factories\Email;
use Symfony\Component\Mime\Email as SymfonyEmail;

class PostmarkTransportTest extends TestCase
{
    public function testTransportSendsMessageSuccessfully()
    {
        $email = Email::createBasic();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getTo())
            ->from($email->getFrom())
            ->html($email->getHtmlBody())
            ->text($email->getTextBody());

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = Http::fake([
            'https://api.postmarkapp.com/email' => Http::response([
                'To' => $email->getTo(),
                'SubmittedAt' => Date::now()->format(DATE_RFC3339_EXTENDED),
                'MessageID' => $email->getMessageId(),
                'ErrorCode' => 0,
                'Message' => 'OK',
            ]),
        ]);

        $this->instance(Factory::class, $factory);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($email->getMessageId(), $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($email) {
            return $request->method() === 'POST'
                && $request->isJson()
                && $request->hasHeader('X-Postmark-Server-Token', $this->getToken())
                && $request['From'] === $email->getFrom()
                && $request['To'] === $email->getTo()
                && $request['Subject'] === $email->getSubject()
                && $request['HtmlBody'] === $email->getHtmlBody()
                && $request['TextBody'] === $email->getTextBody();
        });
    }

    protected function newMessage(): Message
    {
        return new Message(new SymfonyEmail());
    }

    protected function sendMessage(SymfonyEmail $symfonyMessage): ?SentMessage
    {
        return $this->app->makeWith(PostmarkTransport::class, [
            'token' => $this->getToken(),
        ])->send($symfonyMessage, Envelope::create($symfonyMessage));
    }

    protected function getToken(): string
    {
        return $this->app['config']->get('services.postmark.token');
    }
}
