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
     *
     * @return int
     */
    public function send(MessageInterface $message)
    {
        $message->preSend('mail');
        $to = 'To: ' . $message->getToAsString() . Message::LINE_SEPARATOR;
        $subject = $message->getSubjectAsString();
        $body = $message->getMessage();
        $headers = $message->getHeaders();

        if ($message instanceof SwiftMessageAdapter) {
            $recipiens = $this->sendViaSwift($message);
        } else {
            mail($to, $subject, $body, $headers);
            $recipiens = count(explode(',', $to));
        }

        return $recipiens;
    }

    private function sendViaSwift(MessageInterface $message)
    {
        $transport = \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);

        return $mailer->send($message->swiftMessage);
    }
}