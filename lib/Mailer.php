<?php

namespace pyatakss\sendmail;


class Mailer implements MailerInterface
{
    private $transport;

    public $debug = false;

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
     * @return int
     * @throws PSMailException
     */
    public function send(MessageInterface $message)
    {
        try {
            return $this->transport->send($message);
        } catch (PSMailException $e) {
            throw $e;
        }
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