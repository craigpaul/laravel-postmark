<?php

namespace CraigPaul\Mail\Tests;

use Illuminate\Mail\Message;
use Symfony\Component\Mime\Email;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Date;
use CraigPaul\Mail\PostmarkTransport;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use CraigPaul\Mail\Tests\Factories\EmailFactory;

class PostmarkTransportTest extends TestCase
{
    public function testTransportSendsMessageSuccessfully()
    {
        $email = $this->createFakeAttributes();

        $message = $this->newMessage()
            ->subject($email->getSubject())
            ->to($email->getTo())
            ->from($email->getFrom())
            ->text($email->getBody());

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
                && $request['TextBody'] === $email->getBody();
        });
    }

    protected function createFakeAttributes(): EmailFactory
    {
        return EmailFactory::create();
    }

    protected function newMessage(): Message
    {
        return new Message(new Email());
    }

    protected function sendMessage(Email $symfonyMessage): ?SentMessage
    {
        return $this->app->makeWith(PostmarkTransport::class, [
            'token' => $this->getToken(),
        ])->send($symfonyMessage, Envelope::create($symfonyMessage));
    }

    /**
     * @return mixed
     */
    protected function getToken()
    {
        return $this->app['config']->get('services.postmark.token');
    }
}
