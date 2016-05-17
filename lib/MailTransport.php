<?php

namespace pyatakss\sendmail;

class MailTransport extends Transport implements TransportInterface
{
    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param MessageInterface $message
     * @return int
     */
    public function send(MessageInterface $message)
    {
        $messageStr = $message->toString();

        $toArr = $this->getTo($messageStr);
        $to = implode(', ', array_keys($toArr));
        $subject = $message->getSubject();

        $endHeaders = strpos($messageStr, MessageInterface::LINE_SEPARATOR . MessageInterface::LINE_SEPARATOR);
        $headers = substr($messageStr, 0, $endHeaders);
        $body = substr($messageStr, $endHeaders + 4);

        if (!mail($to, $subject, $body, $headers)) {
            return 0;
        } else {
            return count($message->getTo());
        }
    }
}