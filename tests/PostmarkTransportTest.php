<?php

namespace CraigPaul\Mail\Tests;

use Illuminate\Mail\Message;
use Symfony\Component\Mime\Email;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Date;
use CraigPaul\Mail\PostmarkTransport;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Illuminate\Foundation\Testing\WithFaker;

class PostmarkTransportTest extends TestCase
{
    use WithFaker;

    public function testTransportSendsMessageSuccessfully()
    {
        $body = $this->faker->sentences(asText: true);
        $from = $this->faker->email();
        $messageId = $this->faker->uuid();
        $subject = $this->faker->words(asText: true);
        $to = $this->faker->email();

        $message = $this->newMessage()
            ->subject($subject)
            ->to($to)
            ->from($from)
            ->text($body);

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = Http::fake([
            'https://api.postmarkapp.com/email' => Http::response([
                'To' => $to,
                'SubmittedAt' => Date::now()->format(DATE_RFC3339_EXTENDED),
                'MessageID' => $messageId,
                'ErrorCode' => 0,
                'Message' => 'OK',
            ]),
        ]);

        $this->instance(Factory::class, $factory);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($messageId, $sentMessage->getMessageId());
    }

    protected function newMessage(): Message
    {
        return new Message(new Email());
    }

    protected function sendMessage(Email $symfonyMessage): ?SentMessage
    {
        return $this->app->make(PostmarkTransport::class)->send($symfonyMessage, Envelope::create($symfonyMessage));
    }
}
