<?php

namespace pyatakss\sendmail;

class MailTransport implements TransportInterface
{
    public $exceptions = [];

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param MessageInterface $message
     * @return int
     * @throws \Swift_TransportException
     */
    public function send(MessageInterface $message)
    {
        array_merge_recursive($this->exceptions, $message->exceptions);

        $message->preSend('mail');
        $to = 'To: ' . $message->getToAsString() . Message::LINE_SEPARATOR;
        $subject = $message->getSubjectAsString();
        $body = $message->getMessage();
        $headers = $message->getHeaders();

        if ($message instanceof SwiftMessageAdapter) {
            try {
                $recipients = $this->sendViaSwift($message);
            } catch(\Swift_TransportException $e) {
                $this->exceptions[] =  $e;

                return 0;
            }
        } else {
            if (!mail($to, $subject, $body, $headers)) {
                $this->exceptions[] =  'Sending email through the mail() failed.';
            }
            $recipients = count(explode(',', $message->getToAsString()));
        }

        return $recipients;
    }

    private function sendViaSwift(MessageInterface $message)
    {
        $transport = \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);

        return $mailer->send($message->swiftMessage);
    }
}