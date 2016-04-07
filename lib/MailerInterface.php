<?php

namespace pyatakss\sendmail;

interface MailerInterface
{
    /**
     * Create a new Mailer using $transport for delivery.
     *
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport);

    /**
     * Send the given Message.
     *
     * @param MessageInterface $message
     *
     * @return int
     */
    public function send(MessageInterface $message);


    /**
     * The Transport used to send messages.
     *
     * @return TransportInterface
     */
    public function getTransport();

    /**
     * Set Transport
     *
     * @param TransportInterface $transport
     *
     * return void
     */
    public function setTransport(TransportInterface $transport);
}