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
     * @throws \Swift_TransportException
     */
    public function send(MessageInterface $message)
    {
        $message->preSend('mail');
        $to = 'To: ' . $message->getToAsString() . Message::LINE_SEPARATOR;
        $subject = $message->getSubjectAsString();
        $body = $message->getMessage();
        $headers = $message->getHeaders();

        if ($message instanceof SwiftMessageAdapter) {
            try {
                $recipients = $this->sendViaSwift($message);
            } catch(\Swift_TransportException $e) {
                ExceptionHandler::set($e->getMessage() . PHP_EOL . $e->getTraceAsString());

                return 0;
            }
        } else {
            if (!@mail($to, $subject, $body, $headers)) {
                ExceptionHandler::collect(__CLASS__, 'Sending email through the mail() failed.', __FILE__, __LINE__);
                $recipients = 0;
            } else {
                $recipients = count(explode(',', $message->getToAsString()));
            }
        }

        return $recipients;
    }

    private function sendViaSwift(MessageInterface $message)
    {
        $transport = \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);

        try {
            return $mailer->send($message->swiftMessage);
        } catch(\Swift_IoException $e) {
            ExceptionHandler::set($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return 0;
    }
}