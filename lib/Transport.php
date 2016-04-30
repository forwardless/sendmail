<?php

namespace pyatakss\sendmail;

class Transport implements TransportInterface
{
    /**
     * @array
     */
    protected $to;
    /**
     * @string
     */
    protected $toAsString;
    /**
     * @array
     */
    protected $from;
    protected $subject;
    protected $body;
    protected $headers;
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
     * @throws PSMailException
     */
    public function send(MessageInterface $message)
    {
        $this->to = '';
        $this->from = '';
        $this->subject = '';
        $this->body = '';
        $this->headers = '';

        try {
            $message = $message->toString();
        } catch (PSMailException $e) {
            throw $e;
        }

        /* Extract first part of message that includes to, subject, headers */
        if (!preg_match('/^(.*?)(\r\n){2}|(\n){2}|(\r){2}/s', $message, $matches, PREG_OFFSET_CAPTURE) || !isset($matches[1][0])) {
            throw new PSMailException('There is no header.');
        }

        $this->body = mb_substr($message, $matches[2][1]);

        $tempMessage = mb_split('/(\r\n)|\n|\r/', $matches[1][0]);

        /* Store headers */
        for ($i = 0; $i < count($tempMessage); $i++) {

            if (preg_match('/^(Message-ID: )|(Date: )|(MIME-Version: )|(Content-Type: )|(Content-Transfer-Encoding: )/', $tempMessage[$i], $matches)) {
                $this->headers .= $tempMessage[$i] . MessageInterface::LINE_SEPARATOR;
            } elseif (preg_match('/^From: (.+)/', $tempMessage[$i], $from)) {
                $this->from = $from[1];
                $this->headers .= $from[0] . MessageInterface::LINE_SEPARATOR;
            } elseif (preg_match('/^To: (.+)/', $tempMessage[$i], $to)) {
                $this->to = $to[1];
                $this->toAsString = $to[1];
            } elseif (preg_match('/^Subject: (.+)/', $tempMessage[$i], $subject)) {
                $this->subject = $subject[1];
            }
        }
        $this->headers .= MessageInterface::LINE_SEPARATOR;

        $this->to = $this->extractEmailsNames($this->to);
        $this->checkTo();

        $this->from = $this->extractEmailsNames($this->from);
        $this->checkFrom();
    }

    private function checkTo()
    {
        if (!($this instanceof SwiftMessageAdapter) && empty($this->to)) {
            throw new PSMailException('Recipient address does not specified.');
        }

        foreach ($this->to as $email => $name) {
            if (!Message::validationEmail($email)) {
                throw  new PSMailException('Email address is not valid: ' . $email);
            }
        }
    }

    private function checkFrom()
    {
        if (!($this instanceof SwiftMessageAdapter) && empty($this->from)) {
            throw new PSMailException('Sender address does not specified');
        }

        foreach ($this->from as $email => $name) {
            if (!Message::validationEmail($email)) {
                throw  new PSMailException('Email address is not valid: ' . $email);
            }
        }
    }

    /**
     *
     * @param $string
     * @return array
     */
    private function extractEmailsNames($string)
    {
        $tempEmailNamePairs = explode(',', $string);
        $emailNamePairs = [];

        for ($i = 0; $i < count($tempEmailNamePairs); $i++) {
            if (preg_match('/([^<]*)<(.*)>/', $tempEmailNamePairs[$i], $matches)) {
                $email = isset($matches[2]) ? $matches[2] : null;
                $name = isset($matches[1]) ? $matches[1] : null;
                $emailNamePairs[$email] = $name;
            }
        }

        return $emailNamePairs;
    }
}