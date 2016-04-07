<?php

namespace pyatakss\sendmail;


class SMTPTransport implements TransportInterface
{
    private $smtp;
    protected $configuration;

    public function __construct($configuration = null)
    {
        $this->configuration = $configuration;
    }

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
        if ($message instanceof SwiftMessageAdapter) {
            $recipiens = $this->sendViaSwift($message);
        } else {
            $recipiens = $this->sendViaSockets($message);
        }

        return $recipiens;
    }

    private function sendViaSwift(MessageInterface $message)
    {
        $transport = \Swift_SmtpTransport::newInstance($this->configuration['host'], $this->configuration['port'])
            ->setUsername($this->configuration['username'])
            ->setPassword($this->configuration['password']);
        $mailer = \Swift_Mailer::newInstance($transport);

        return $mailer->send($message->swiftMessage);
    }

    private function sendViaSockets(MessageInterface $message)
    {
        $message->preSend('smtp');
        $to = $message->getToAsString();
        $from = $message->getFromAsString();
        $subject = $message->getSubjectAsString();
        $body = $message->getMessage();
        $headers = $message->getHeaders();

        $smtp_host = $this->configuration['host'];
        $smtp_port = $this->configuration['port'];
        $user = $this->configuration['username'];
        $pass = $this->configuration['password'];

        if (!($socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15))) {
            echo "Error connecting to '$smtp_host' ($errno) ($errstr)" . PHP_EOL;

            return false;
        }

        $this->serverParse($socket, '220');

        fwrite($socket, 'EHLO smtp.gmail.com' . Message::LINE_SEPARATOR);
        $this->serverParse($socket, '250', __LINE__);

        fwrite($socket, 'AUTH LOGIN' . Message::LINE_SEPARATOR);
        $this->serverParse($socket, '334', __LINE__);

        fwrite($socket, base64_encode($user) . Message::LINE_SEPARATOR);
        $this->serverParse($socket, '334', __LINE__);

        fwrite($socket, base64_encode($pass) . Message::LINE_SEPARATOR);
        $this->serverParse($socket, '235', __LINE__);

        fwrite($socket, 'MAIL FROM: <' . $user . '>' . Message::LINE_SEPARATOR);
        $this->serverParse($socket, '250', __LINE__);

        foreach ($message->getTo() as $email => $name) {
            fwrite($socket, 'RCPT TO: <' . $email . '>' . Message::LINE_SEPARATOR);
            $this->serverParse($socket, '250', __LINE__);
        }

        fwrite($socket, 'DATA' . Message::LINE_SEPARATOR);
        $this->serverParse($socket, '354', __LINE__);

        fwrite($socket, 'Subject: ' . $subject . Message::LINE_SEPARATOR
            . 'To: ' . $to . Message::LINE_SEPARATOR
            . $headers . Message::LINE_SEPARATOR . Message::LINE_SEPARATOR
            . $body . Message::LINE_SEPARATOR);

        fwrite($socket, '.' . Message::LINE_SEPARATOR);
        $this->serverParse($socket, '250', __LINE__);

        fwrite($socket, 'QUIT' . Message::LINE_SEPARATOR);
        fclose($socket);

        return count(explode(',', $to));
    }

    //Functin to Processes Server Response Codes
    private function serverParse($socket, $expected_response, $line = null)
    {
        $server_response = '';
        while (substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                echo 'Error while fetching server response codes. ', __FILE__, ' ', $line, PHP_EOL;
            }
        }

        if (!(substr($server_response, 0, 3) == $expected_response)) {
            echo 'ERROR --> "' . $server_response . '"', __FILE__, ' ', $line, PHP_EOL;

            return false;
        }
    }

    public function beforeSend()
    {
        $socket = fsockopen("ssl://smtp.gmail.com", 465, $errno, $errstr, 10);
        if (!$socket) {
            echo "ERROR: smtp.gmail.com 465 - $errstr ($errno)<br>\n";
        } else {
            echo "SUCCESS: smtp.gmail.com 465 - ok<br>\n";
        }

        $socket = fsockopen("smtp.gmail.com", 587, $errno, $errstr, 10);
        if (!$socket) {
            echo "ERROR: smtp.gmail.com 587 - $errstr ($errno)<br>\n";
        } else {
            echo "SUCCESS: smtp.gmail.com 587 - ok<br>\n";
        }
    }
}