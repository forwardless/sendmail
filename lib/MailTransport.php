<?php

namespace pyatakss\sendmail;

class MailTransport implements TransportInterface
{
    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param MessageInterface $message
     * @return int
     * @throws PSMailException
     */
    public function send(MessageInterface $message)
    {
        $recipients = 0;
        try {
            $to = $message->getToAsString(true);
            $subject = $message->getSubjectAsString();
            $body = $message->getMessage();
            $headers = $message->getHeaders();
        } catch (PSMailException $e) {
            throw $e;
        }

        if ($message instanceof SwiftMessageAdapter) {
            try {
                $recipients = $this->sendViaSwift($message);
            } catch (\Swift_TransportException $e) {
                throw new PSMailException($e);
            }
        } else {
            if (!@mail($to, $subject, $body, $headers)) {
                throw new PSMailException('Send email through the mail() failed.');
            } else {
                $recipients = count($message->getTo());
            }
        }

        return $recipients;
    }

    /**
     * @param MessageInterface $message
     * @return int
     *
     * @throws PSMailException
     */
    private function sendViaSwift(MessageInterface $message)
    {
        $transport = \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);

        try {
            return $mailer->send($message->swiftMessage);
        } catch (\Swift_IoException $e) {
            throw new PSMailException($e);
        }
    }
}