<?php

namespace pyatakss\sendmail;

use Mandrill;

class MandrillTransport implements TransportInterface
{
    protected $configuration;
    private $mandrill;

    public function __construct($configuration = null)
    {
        $this->configuration = $configuration;
        $this->mandrill = new Mandrill($configuration);
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param MessageInterface $message
     * @return int
     * @throws \Mandrill_Error
     */
    public function send(MessageInterface $message)
    {
        $recipiens = $this->sendViaMandrillRaw($message);

        return $recipiens;
    }

    private function sendViaMandrillRaw($message)
    {
        foreach ($message->getTo() as $address => $name) {
            $to[] = $address;
        }
        if (!isset($to)) {
            ExceptionHandler::collect(__CLASS__, 'Cannot send message without a recipient', __FILE__, __LINE__);
            return 0;
        }

        foreach ($message->getFrom() as $address => $name) {
            $from_email = $address;
            $from_name = ($name) ? $name : '';
        }
        if (!isset($from_email)) {
            ExceptionHandler::collect(__CLASS__, 'Cannot send message without a sender', __FILE__, __LINE__);
            return 0;
        }

        try {
            $raw_message = $message->toString('mandrill_raw');
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = date(DATE_RFC2822);
            $return_path_domain = null;
            $result = $this->mandrill->messages->sendRaw($raw_message, $from_email, $from_name, $to, $async, $ip_pool, $send_at, $return_path_domain);
        } catch (\Mandrill_Error $e) {
            ExceptionHandler::collect(__CLASS__, 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage(), __FILE__, __LINE__);

            return 0;
        }

        return count($to);
    }

    public function beforeSend()
    {
        $result = $this->mandrill->users->info();
        print_r($result);
    }

    public function infoSend($id = null)
    {
        try {
            $result = $this->mandrill->messages->info($id);
            print_r($result);
        } catch (\Mandrill_Error $e) {
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            throw $e;
        }

    }
}