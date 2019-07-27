<?php

namespace Coconuts\Mail;

use Illuminate\Notifications\Messages\MailMessage as Message;

class MailMessage extends Message
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    public $view = 'postmark::template';

    /**
     * Set the template alias.
     *
     * @param  string  $alias
     * @return $this
     */
    public function alias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get the data array for the mail message.
     *
     * @return array
     */
    public function data()
    {
        return [
            'id' => $this->id,
            'alias' => $this->alias,
            'model' => $this->data,
        ];
    }

    /**
     * Set the template identifier.
     *
     * @param  int  $id
     * @return $this
     */
    public function identifier($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the data to be available within the template.
     *
     * @param  array  $data
     * @return $this
     */
    public function include($data)
    {
        $this->data = $data;

        return $this;
    }
}
