<?php

namespace CraigPaul\Mail;

use Symfony\Component\Mime\Address;
use Illuminate\Http\Client\Factory as Http;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mailer\Transport\TransportInterface;
use function implode;
use function array_map;

class PostmarkTransport implements TransportInterface
{
    public function __construct(
        protected Http $http,
        protected string $token,
    ) {
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $sentMessage = new SentMessage($message, $envelope ?? Envelope::create($message));

        $email = MessageConverter::toEmail($sentMessage->getOriginalMessage());

        $response = $this->http
            ->acceptJson()
            ->withHeaders([
                'X-Postmark-Server-Token' => $this->token,
            ])
            ->post('https://api.postmarkapp.com/email', [
                'From' => $envelope->getSender()->toString(),
                'To' => implode(',', array_map(fn (Address $address) => $address->toString(), $envelope->getRecipients())),
                'Subject' => $email->getSubject(),
                'HtmlBody' => $email->getHtmlBody(),
                'TextBody' => $email->getTextBody(),
            ]);

        $sentMessage->setMessageId($response->json('MessageID'));

        return $sentMessage;
    }

    public function __toString(): string
    {
        return 'postmark';
    }
}
