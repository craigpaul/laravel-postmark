<?php

namespace CraigPaul\Mail;

use Illuminate\Http\Client\Factory as Http;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;

class PostmarkTransport implements TransportInterface
{
    public function __construct(
        protected Http $http,
    ) {
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $sentMessage = new SentMessage($message, $envelope ?? Envelope::create($message));

        $response = $this->http->post('https://api.postmarkapp.com/email', []);

        $sentMessage->setMessageId($response->json('MessageID'));

        return $sentMessage;
    }

    public function __toString(): string
    {
        return 'postmark';
    }
}
