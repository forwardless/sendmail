<?php

namespace pyatakss\sendmail;

use Mandrill;

class MandrillTransport extends Transport implements TransportInterface
{
    private $mandrill;

    public function __construct($configuration = null)
    {
        parent::__construct($configuration);

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
     * @throws PSMailException
     */
    public function send(MessageInterface $message)
    {
        $recipients = $this->sendViaMandrillRaw($message);

        return $recipients;
    }

    /**
     * @param $message
     * @return mixed
     * @throws \Mandrill_Error
     */
    private function sendViaMandrillRaw($message)
    {
        foreach ($this->to as $address => $name) {
            $to[] = $address;
        }

        foreach ($this->from as $address => $name) {
            $from_email = $address;
            $from_name = ($name) ? $name : '';
        }
        $raw_message = $message->toString();

        $async = false;
        $ip_pool = 'Main Pool';
        $send_at = date(DATE_RFC2822);
        $return_path_domain = null;
        $result = $this->mandrill->messages->sendRaw($raw_message, $from_email, $from_name, $to, $async, $ip_pool, $send_at, $return_path_domain);

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