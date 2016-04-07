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
     * @throws Mandrill_Error
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
            $to = ['example@example.com'];
        }

        $from_email = 'pyatak_ss@groupbwt.com';
        $from_name = '';
        foreach ($message->getFrom() as $address => $name) {
            $from_email = $address;
            $from_name = "=?UTF-8?B?" . $name . '?=';
        }

        try {
            $raw_message = $message->toString();
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = date(DATE_RFC2822);
            $return_path_domain = null;
            $result = $this->mandrill->messages->sendRaw($raw_message, $from_email, $from_name, $to, $async, $ip_pool, $send_at, $return_path_domain);
            print_r($result);
        } catch (Mandrill_Error $e) {
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();

            throw $e;
        }

        return count($to);
    }

    private function sendViaMandrill(MessageInterface $message)
    {
        $message->preSend('mandrill');
        $to = [];
        $subject = $message->getSubjectAsString();
        $body = $message->getMessage();
        $headers = $message->getHeaders();

        foreach ($message->getTo() as $address => $name) {
            $to[] = [
                'email' => $address,
                'name' => $name,
                'type' => 'to',
            ];
        }
        $from_email = 'pyatak_ss@groupbwt.com';
        $from_name = '';
        foreach ($message->getFrom() as $address => $name) {
            $from_email = $address;
            $from_name = "=?UTF-8?B?" . $name . '?=';
        }

        $attach = [];
        foreach ($message->getAttach() as $file => $options) {
            $attach[] = [
                'type' => $options['mime_type'],
                'name' => $options['name'],
                'content' => $message->getFile($file),
            ];
        }

        try {
            $messageMandrill = [
                'html' => $body,
                'text' => 'Example text content',
                'subject' => $subject,
                'from_email' => $from_email,
                'from_name' => $from_name,
                'to' => [$to],
                'headers' => [$headers],
                'important' => false,
                'track_opens' => null,
                'track_clicks' => null,
                'auto_text' => null,
                'auto_html' => null,
                'inline_css' => null,
                'url_strip_qs' => null,
                'preserve_recipients' => null,
                'view_content_link' => null,
                'bcc_address' => '',
                'tracking_domain' => null,
                'signing_domain' => null,
                'return_path_domain' => null,
                'merge' => false,
                'tags' => ['test-message'],
                'google_analytics_domains' => ['example.com'],
                'google_analytics_campaign' => 'message.from_email@example.com',
                'metadata' => [],
                'attachments' => [$attach],
                'images' => null,
            ];
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = date(DATE_RFC2822);
            $result = $this->mandrill->messages->send($messageMandrill, $async, $ip_pool, $send_at);
            print_r($result);
        } catch (Mandrill_Error $e) {
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            throw $e;
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
        } catch (Mandrill_Error $e) {
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            throw $e;
        }

    }
}