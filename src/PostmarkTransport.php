<?php

namespace Coconuts\Mail;

use Coconuts\Mail\Exceptions\PostmarkException;
use function collect;
use function json_decode;
use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Swift_Attachment;
use Swift_Mime_SimpleMessage;
use Swift_MimePart;

class PostmarkTransport extends Transport
{
    /** @var \GuzzleHttp\ClientInterface */
    protected $client;

    /** @var string */
    protected $key;

    /**
     * @throws \Coconuts\Mail\Exceptions\PostmarkException
     */
    public function __construct(ClientInterface $client, ?string $key)
    {
        if (empty(trim($key))) {
            throw new PostmarkException(
                'The Postmark secret is not set. Make sure that the `postmark.secret` config key is set.'
            );
        }

        $this->key = $key;
        $this->client = $client;
    }

    public function getApiEndpoint(Swift_Mime_SimpleMessage $message): string
    {
        if ($this->templated($message)) {
            return '/email/withTemplate';
        }

        return '/email';
    }

    /**
     * Send the given message.
     *
     * @param  Swift_Mime_SimpleMessage  $message
     * @param  array  $failedRecipients
     * @return int
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $this->beforeSendPerformed($message);

        $response = $this->client->request(
            'POST',
            $this->getApiEndpoint($message),
            $this->payload($message)
        );

        $messageId = $this->getMessageId($response);

        $message->getHeaders()->addTextHeader('X-Message-ID', $messageId);
        $message->getHeaders()->addTextHeader('X-PM-Message-Id', $messageId);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    protected function getAttachments(Swift_Mime_SimpleMessage $message): array
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

    protected function getDisplayName(string $value): string
    {
        if (Str::contains($value, ',')) {
            return '"'.$value.'"';
        }

        return $value;
    }

    /**
     * Format the contacts for the API request.
     *
     * @param  string|array  $contacts
     * @return string
     */
    protected function getContacts($contacts): string
    {
        return collect($contacts)
            ->map(function ($display, $address) {
                return $display ? $this->getDisplayName($display)." <{$address}>" : $address;
            })
            ->values()
            ->implode(',');
    }

    protected function getMessageId(ResponseInterface $response): string
    {
        return object_get(
            json_decode($response->getBody()->getContents()),
            'MessageID'
        );
    }

    protected function getBody(Swift_Mime_SimpleMessage $message): string
    {
        return $message->getBody() ?: '';
    }

    protected function getHtmlAndTextBody(Swift_Mime_SimpleMessage $message): array
    {
        $types = [
            'text/html' => 'HtmlBody',
            'multipart/mixed' => 'HtmlBody',
            'multipart/related' => 'HtmlBody',
            'multipart/alternative' => 'HtmlBody',
        ];

        $key = collect($types)->get($message->getContentType(), 'TextBody');

        return collect([$key => $this->getBody($message)])
            ->when($this->getMimePart($message, 'text/plain'), function ($collection, $value) {
                return $collection->put('TextBody', $value->getBody());
            })
            ->when($this->getMimePart($message, 'text/html'), function ($collection, $value) {
                return $collection->put('HtmlBody', $value->getBody());
            })
            ->all();
    }

    protected function getMimePart(Swift_Mime_SimpleMessage $message, string $mimeType): ?Swift_MimePart
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

    protected function getSubject(Swift_Mime_SimpleMessage $message): string
    {
        return $message->getSubject() ?: '';
    }

    protected function getHeaders(Swift_Mime_SimpleMessage $message): array
    {
        return collect($message->getHeaders()->getAll())
            ->reject(function ($header) {
                return Str::startsWith($header->getFieldName(), 'metadata-') ||
                    $header->getFieldName() == 'Message-ID' && Str::contains($header->getFieldBody(), 'swift.generated') ||
                    collect([
                        'To',
                        'Cc',
                        'Bcc',
                        'Tag',
                        'Date',
                        'From',
                        'Subject',
                        'Reply-To',
                        'Content-Type',
                        'MIME-Version',
                    ])->contains($header->getFieldName());
            })
            ->map(function ($header) {
                return [
                    'Name' => $header->getFieldName(),
                    'Value' => $header->getFieldBody(),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getMetadata(Swift_Mime_SimpleMessage $message): array
    {
        return collect($message->getHeaders()->getAll())
            ->filter(function ($header) {
                return Str::startsWith($header->getFieldName(), 'metadata-');
            })
            ->mapWithKeys(function ($header) {
                return [
                    Str::after($header->getFieldName(), 'metadata-') => iconv_mime_decode($header->getFieldBody(), 0, 'UTF-8'),
                ];
            })
            ->toArray();
    }

    protected function getTag(Swift_Mime_SimpleMessage $message): string
    {
        $tags = collect($message->getHeaders()->getAll('tag'));

        return optional($tags->last())->getFieldBody() ?: '';
    }

    protected function payload(Swift_Mime_SimpleMessage $message): array
    {
        $headers = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Server-Token' => $this->key,
            ],
        ];

        $json = [
            'Cc' => $this->getContacts($message->getCc()),
            'Bcc' => $this->getContacts($message->getBcc()),
            'Tag' => $this->getTag($message),
            'Headers' => $this->getHeaders($message),
            'Metadata' => $this->getMetadata($message),
            'ReplyTo' => $this->getContacts($message->getReplyTo()),
            'Attachments' => $this->getAttachments($message),
        ];

        if ($contents = $this->templated($message)) {
            $json['TemplateId'] = $contents['id'] ?? null;
            $json['TemplateAlias'] = $contents['alias'] ?? null;
            $json['TemplateModel'] = $contents['model'] ?? null;
        }

        return collect($headers)
            ->merge([
                'json' => collect($json)
                    ->reject(function ($item) {
                        return empty($item);
                    })
                    ->put('From', $this->getContacts($message->getFrom()))
                    ->put('To', $this->getContacts($message->getTo()))
                    ->when($contents === null, function (Collection $collection) use ($message) {
                        return $collection
                            ->merge($this->getHtmlAndTextBody($message))
                            ->merge(['Subject' => $this->getSubject($message)]);
                    }),
            ])
            ->toArray();
    }

    protected function templated(Swift_Mime_SimpleMessage $message): ?array
    {
        return json_decode($message->getBody(), JSON_OBJECT_AS_ARRAY);
    }
}
