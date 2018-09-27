<?php

namespace Coconuts\Mail;

use Swift_MimePart;
use Swift_Attachment;
use Swift_Mime_SimpleMessage;
use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Coconuts\Mail\Exceptions\PostmarkException;

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
     * @throws \Coconuts\Mail\Exceptions\PostmarkException
     */
    public function __construct(ClientInterface $client, $key)
    {
        if (empty(trim($key))) {
            throw new PostmarkException(
                'The Postmark secret is not set. Make sure that the `postmark.secret` config key is set.'
            );
        }

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
            ->values()
            ->toArray();
    }

    /**
     * Format the display name.
     *
     * @param  string
     * @return string
     */
    protected function getDisplayname($value)
    {
        if (strpos($value, ',') !== false) {
            return '"'.$value.'"';
        }

        return $value;
    }

    /**
     * Format the contacts for the API request.
     *
     * @param string|array $contacts
     *
     * @return string
     */
    protected function getContacts($contacts)
    {
        return collect($contacts)
            ->map(function ($display, $address) {
                return $display ? $this->getDisplayname($display)." <{$address}>" : $address;
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
    protected function getBody(Swift_Mime_SimpleMessage $message)
    {
        return $message->getBody() ?: '';
    }

    /**
     * Get the text and html fields for the given message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function getHtmlAndTextBody(Swift_Mime_SimpleMessage $message)
    {
        $key = collect([
            'text/html' => 'HtmlBody',
            'multipart/mixed' => 'HtmlBody',
            'multipart/related' => 'HtmlBody',
            'multipart/alternative' => 'HtmlBody',
        ])
        ->get($message->getContentType(), 'TextBody');

        return collect([
            $key => $this->getBody($message),
        ])->when($this->getMimePart($message, 'text/plain'), function ($collection, $value) {
            return $collection->put('TextBody', $value->getBody());
        })->when($this->getMimePart($message, 'text/html'), function ($collection, $value) {
            return $collection->put('HtmlBody', $value->getBody());
        })->all();
    }

    /**
     * Get a mime part from the given message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @param string $mimeType
     *
     * @return \Swift_MimePart|null
     */
    protected function getMimePart(Swift_Mime_SimpleMessage $message, $mimeType)
    {
        return collect($message->getChildren())
            ->filter(function ($child) {
                return $child instanceof Swift_MimePart;
            })
            ->filter(function ($child) use ($mimeType) {
                return strpos($child->getContentType(), $mimeType) === 0;
            })
            ->first();
    }

    /**
     * Get the subject for the given message.
     *
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getSubject(Swift_Mime_SimpleMessage $message)
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
    protected function getTag(Swift_Mime_SimpleMessage $message)
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
            ],
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
            ->merge($this->getHtmlAndTextBody($message)),
        ])
        ->toArray();
    }
}
