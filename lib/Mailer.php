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
     *
     * @return int
     */
    public function send(MessageInterface $message)
    {
        $to = $message->getTo();
        $from = $message->getFrom();
        if (empty($to)) {
            ExceptionHandler::collect(__CLASS__, 'Cannot send message without a recipient', __FILE__, __LINE__);

            return 0;
        }
        if (empty($from)) {
            ExceptionHandler::collect(__CLASS__, 'Cannot send message without a sender', __FILE__, __LINE__);

            return 0;
        }

        return $this->transport->send($message);
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

    public function getExceptions()
    {
        return ExceptionHandler::get();
    }
}