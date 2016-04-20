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
        try {
            foreach ($message->getTo() as $address => $name) {
                $to[] = $address;
            }

            foreach ($message->getFrom() as $address => $name) {
                $from_email = $address;
                $from_name = ($name) ? $name : '';
            }
            $raw_message = $message->toString();
        } catch (PSMailException $e) {
            throw $e;
        }

        try {
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = date(DATE_RFC2822);
            $return_path_domain = null;
            $result = $this->mandrill->messages->sendRaw($raw_message, $from_email, $from_name, $to, $async, $ip_pool, $send_at, $return_path_domain);
        } catch (\Mandrill_Error $e) {
            throw new PSMailException($e);
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