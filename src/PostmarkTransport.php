<?php

namespace Coconuts\Mail;

use Swift_Attachment;
use Swift_Mime_SimpleMessage;
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
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $response = $this->client->post($this->url, $this->payload($message));

        $message->getHeaders()->addTextHeader(
            'X-PM-Message-Id',
            $this->getMessageId($response)
        );

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get all attachments for the given message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function getAttachments(Swift_Mime_SimpleMessage $message)
    {
        return collect($message->getChildren())
            ->filter(function ($child) {
                return $child instanceof Swift_Attachment;
            })
            ->map(function ($child) {
                return [
                    'Name' => $child->getHeaders()->get('content-type')->getParameter('name'),
                    'Content' => base64_encode($child->getBody()),
                    'ContentType' => $child->getContentType(),
                ];
            })
            ->toArray();
    }

    /**
     * Format the contacts for the API request.
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
     * Get the message ID from the response.
     *
     * @param \GuzzleHttp\Psr7\Response $response
     *
     * @return string
     */
    protected function getMessageId($response)
    {
        return object_get(
            json_decode($response->getBody()->getContents()),
            'MessageID'
        );
    }

    /**
     * Get the body for the given message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getBody($message)
    {
        return $message->getBody() ?: '';
    }

    /**
     * Get the subject for the given message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getSubject($message)
    {
        return $message->getSubject() ?: '';
    }

    /**
     * Get the tag for the given message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getTag($message)
    {
        return optional(
            collect($message->getHeaders()->getAll('tag'))
            ->last()
        )
        ->getFieldBody() ?: '';
    }

    /**
     * Get the HTTP payload for sending the Postmark message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function payload(Swift_Mime_SimpleMessage $message)
    {
        return collect([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Server-Token' => $this->key,
            ]
        ])
        ->merge([
            'json' => collect([
                'Cc' => $this->getContacts($message->getCc()),
                'Bcc' => $this->getContacts($message->getBcc()),
                'Tag' => $this->getTag($message),
                'Subject' => $this->getSubject($message),
                'ReplyTo' => $this->getContacts($message->getReplyTo()),
                'Attachments' => $this->getAttachments($message),
            ])
            ->reject(function ($item) {
                return empty($item);
            })
            ->put('From', $this->getContacts($message->getFrom()))
            ->put('To', $this->getContacts($message->getTo()))
            ->put('HtmlBody', $this->getBody($message))
        ])
        ->toArray();
    }
}
