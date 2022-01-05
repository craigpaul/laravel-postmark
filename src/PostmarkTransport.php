<?php

namespace CraigPaul\Mail;

use function array_filter;
use function array_map;
use function array_merge;
use Illuminate\Http\Client\Factory as Http;
use function implode;
use function in_array;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

class PostmarkTransport implements TransportInterface
{
    public function __construct(
        protected Http $http,
        protected string $token,
    ) {
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $envelope = $envelope ?? Envelope::create($message);

        $sentMessage = new SentMessage($message, $envelope);

        $email = MessageConverter::toEmail($sentMessage->getOriginalMessage());

        $response = $this->http
            ->acceptJson()
            ->withHeaders([
                'X-Postmark-Server-Token' => $this->token,
            ])
            ->post('https://api.postmarkapp.com/email', [
                'From' => $envelope->getSender()->toString(),
                'To' => $this->stringifyAddresses($this->getRecipients($email, $envelope)),
                'Cc' => $this->stringifyAddresses($email->getCc()),
                'Bcc' => $this->stringifyAddresses($email->getBcc()),
                'Subject' => $email->getSubject(),
                'HtmlBody' => $email->getHtmlBody(),
                'TextBody' => $email->getTextBody(),
            ]);

        $sentMessage->setMessageId($response->json('MessageID'));

        return $sentMessage;
    }

    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        $copies = array_merge($email->getCc(), $email->getBcc());

        return array_filter($envelope->getRecipients(), function (Address $address) use ($copies) {
            return in_array($address, $copies, true) === false;
        });
    }

    protected function stringifyAddresses(array $addresses): string
    {
        return implode(',', array_map(fn (Address $address) => $address->toString(), $addresses));
    }

    public function __toString(): string
    {
        return 'postmark';
    }
}
