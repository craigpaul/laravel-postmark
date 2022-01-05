<?php

namespace CraigPaul\Mail\Tests\Notifications;

use CraigPaul\Mail\TemplatedMailMessage;
use Illuminate\Notifications\Notification;
use CraigPaul\Mail\Tests\Factories\Template;
use Illuminate\Notifications\Messages\MailMessage;

class TemplatedNotification extends Notification
{
    public function __construct(
        protected Template $template
    ) {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new TemplatedMailMessage())
            ->alias($this->template->getAlias())
            ->identifier($this->template->getId())
            ->include($this->template->getModel());
    }
}
