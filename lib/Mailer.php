<?php

namespace pyatakss\sendmail;


class Mailer implements MailerInterface
{
    private $transport;

    public $debug = false;
    public $exceptions = [];

    /**
     * Create a new Mailer using $transport for delivery.
     *
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Send the given Message.
     *
     * @param MessageInterface $message
     *
     * @return int
     */
    public function send(MessageInterface $message)
    {
        $to = $message->getTo();
        $from = $message->getFrom();
        if (empty($to)) {
            $this->exceptions[] =  'Cannot send message without a recipient.';

            return 0;
        }
        if (empty($from)) {
            $this->exceptions[] =  'Cannot send message without a sender.';

            return 0;
        }

        $recipients = $this->transport->send($message);
        array_merge_recursive($this->exceptions, $message->exceptions, $this->transport->exceptions);

        return $recipients;
    }

    /**
     * The Transport used to send messages.
     *
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set Transport
     *
     * @param TransportInterface $transport
     *
     * return void
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }
}