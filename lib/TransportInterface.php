<?php

namespace pyatakss\sendmail;

interface TransportInterface
{
    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param MessageInterface $message
     *
     * @return int
     */
    public function send(MessageInterface $message);

}