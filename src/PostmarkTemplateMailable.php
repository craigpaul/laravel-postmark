<?php

namespace Coconuts\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PostmarkTemplateMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $id;
    public $alias;
    public $model;

    /**
     * Create a new message instance.
     *
     * @param string $alias - The alias of the Postmark template.
     * @param array $data - An associated array of data to use in the template.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->view('postmark::template');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this;
    }

    /**
     * Set the template via the alias.
     * 
     * @param string $alias 
     * @return PostmarkTemplateMailable 
     */
    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get the template data.
     * 
     * @return array 
     */
    public function data(): array
    {
        return [
            'id' => $this->id,
            'alias' => $this->alias,
            'model' => $this->model,
        ];
    }

    /**
     * Set the template via the identifier.
     * 
     * @param int $id 
     * @return PostmarkTemplateMailable 
     */
    public function identifier(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the data for the template variables.
     * 
     * @param array $data 
     * @return PostmarkTemplateMailable 
     */
    public function include(array $data): self
    {
        $this->model = $data;

        return $this;
    }
}
