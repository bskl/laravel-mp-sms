<?php

namespace Bskl\MpSms\Channels\Messages;

class MpSmsMessage
{
    /**
     * The message content.
     *
     * @var string
     */
    public $content;

    /**
     * The phone number the message should be sent from.
     *
     * @var string
     */
    public $from;

    /**
     * The message logging status.
     *
     * @var bool
     */
    public $logging;

    /**
     * Create a new message instance.
     *
     * @param string $content
     *
     * @return void
     */
    public function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the phone number the message should be sent from.
     *
     * @param string $from
     *
     * @return $this
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the message's logging status.
     *
     * @param bool $logging
     *
     * @return $this
     */
    public function logging($logging)
    {
        $this->logging = $logging;

        return $this;
    }
}
