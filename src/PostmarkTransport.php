<?php

namespace Coconuts\Mail;

use Swift_Mime_Message;
use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;

class PostmarkTransport extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The Postmark API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The Postmark API end-point.
     *
     * @var string
     */
    protected $url = 'https://api.postmarkapp.com/email';

    /**
     * Create a new Postmark transport instance.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string $key
     *
     * @return void
     */
    public function __construct(ClientInterface $client, $key)
    {
        $this->key = $key;
        $this->client = $client;
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $this->client->post($this->url, $this->payload($message));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Format the contacts for the API request
     *
     * @param array $contacts
     *
     * @return string
     */
    protected function getContacts($contacts)
    {
        return collect($contacts)
            ->map(function ($display, $address) {
                return $display ? $display . " <{$address}>" : $address;
            })
            ->values()
            ->implode(',');
    }

    /**
     * Get the "From" payload field for the API request.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return string
     */
    protected function getFrom(Swift_Mime_Message $message)
    {
        return collect($message->getFrom())
            ->map(function ($display, $address) {
                return $display ? $display . " <$address>" : $address;
            })
            ->values()
            ->implode(',');
    }

    /**
     * Get the HTTP payload for sending the Postmark message.
     *
     * @param \Swift_Mime_Message $message
     *
     * @return array
     */
    protected function payload(Swift_Mime_Message $message)
    {
        $headers = $message->getHeaders();

        $to = $this->getContacts($message->getTo());
        $cc = $this->getContacts($message->getCc());
        $bcc = $this->getContacts($message->getBcc());
        $replyTo = $this->getContacts($message->getReplyTo());

        return [
            'headers' => [
                'Accept' => 'application/json',
                'X-Postmark-Server-Token' => $this->key,
            ],
            'json' => [
                'From' => $this->getFrom($message),
                'To' => $to,
                'Cc' => $cc,
                'Bcc' => $bcc,
                'Tag' => $headers->has('tag') ? $headers->get('tag')->getFieldBody() : '',
                'Subject' => $message->getSubject(),
                'HtmlBody' => $message->getBody(),
                'ReplyTo' => $replyTo,
            ],
        ];
    }
}
