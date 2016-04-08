<?php

namespace pyatakss\sendmail;


class SMTPTransport implements TransportInterface
{
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

    /**
     * @param MessageInterface $message
     * @return mixed
     */
    private function sendViaSwift(MessageInterface $message)
    {
        $transport = \Swift_SmtpTransport::newInstance($this->configuration['host'], $this->configuration['port'])
            ->setUsername($this->configuration['username'])
            ->setPassword($this->configuration['password']);
        $mailer = \Swift_Mailer::newInstance($transport);

        return $mailer->send($message->swiftMessage);
    }

    /**
     * @param MessageInterface $message
     * @return int
     */
    private function sendViaSockets(MessageInterface $message)
    {
        if (!array_key_exists('host', $this->configuration) || !array_key_exists('port', $this->configuration) || !array_key_exists('username', $this->configuration) || !array_key_exists('password', $this->configuration)) {
            ExceptionHandler::collect(__CLASS__, 'Configuration failed', __FILE__, __LINE__);

            return 0;
        }

        $message->preSend('smtp');
        $to = $message->getToAsString();
        foreach ($message->getFrom() as $address => $name) {
            $from_email = $address;
            $from_name = $name;
        }
        $subject = $message->getSubjectAsString();
        $body = $message->getMessage();
        $headers = $message->getHeaders();

        $smtp_host = $this->configuration['host'];
        $smtp_port = $this->configuration['port'];
        $user = $this->configuration['username'];
        $pass = $this->configuration['password'];

        $from_email = (isset($from_email) && !empty($from_email)) ? $from_email : $user;

        if (!($socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15))) {
            ExceptionHandler::collect(__CLASS__, "Error connecting to '$smtp_host' ($errno) ($errstr)", __FILE__, __LINE__);

            return 0;
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

        fwrite($socket, 'MAIL FROM: <' . $from_email . '>' . Message::LINE_SEPARATOR);
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

    /**
     * @param $socket
     * @param $expected_response
     * @param null $line
     * @return bool
     */
    private function serverParse($socket, $expected_response, $line = null)
    {
        $server_response = '';
        while (substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                ExceptionHandler::collect(__CLASS__, 'Error while fetching server response codes', __FILE__, __LINE__);
            }
        }

        if (!(substr($server_response, 0, 3) == $expected_response)) {
            ExceptionHandler::collect(__CLASS__, $server_response, __FILE__, __LINE__);

            return 0;
        }
    }

    /**
     *
     */
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