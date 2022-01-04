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
use Illuminate\Foundation\Testing\WithFaker;

class PostmarkTransportTest extends TestCase
{
    use WithFaker;

    public function testTransportSendsMessageSuccessfully()
    {
        $attributes = $this->createFakeAttributes();

        $message = $this->newMessage()
            ->subject($attributes['subject'])
            ->to($attributes['to'])
            ->from($attributes['from'])
            ->text($attributes['body']);

        $symfonyMessage = $message->getSymfonyMessage();

        $factory = Http::fake([
            'https://api.postmarkapp.com/email' => Http::response([
                'To' => $attributes['to'],
                'SubmittedAt' => Date::now()->format(DATE_RFC3339_EXTENDED),
                'MessageID' => $attributes['messageId'],
                'ErrorCode' => 0,
                'Message' => 'OK',
            ]),
        ]);

        $this->instance(Factory::class, $factory);

        $sentMessage = $this->sendMessage($symfonyMessage);

        $this->assertSame($attributes['messageId'], $sentMessage->getMessageId());

        $factory->assertSent(function (Request $request) use ($attributes) {
            return $request->method() === 'POST'
                && $request->isJson()
                && $request->hasHeader('X-Postmark-Server-Token', $this->getToken())
                && $request['From'] === $attributes['from']
                && $request['To'] === $attributes['to']
                && $request['Subject'] === $attributes['subject']
                && $request['TextBody'] === $attributes['body'];
        });
    }

    protected function createFakeAttributes(): array
    {
        return [
            'body' => $this->faker->sentences(asText: true),
            'from' => $this->faker->email(),
            'messageId' => $this->faker->uuid(),
            'subject' => $this->faker->words(asText: true),
            'to' => $this->faker->email(),
        ];
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
