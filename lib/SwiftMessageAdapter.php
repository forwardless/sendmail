<?php

namespace pyatakss\sendmail;

class SwiftMessageAdapter extends Message implements MessageInterface
{
    public $swiftMessage;

    public function __construct(\Swift_Message $swift_message)
    {
        parent::__construct();
        $this->swiftMessage = $swift_message;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->swiftMessage->getTo();
    }

    /**
     * @return array
     */
    public function getFrom()
    {
        return $this->swiftMessage->getFrom();
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->swiftMessage->getSubject();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->swiftMessage->getBody();
    }

    /**
     * @return array
     */
    public function getAttach()
    {

    }

    /**
     * Get the character set of this message.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->swiftMessage->getCharset();
    }

    /**
     * Get the Content-type of this message.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->swiftMessage->getContentType();
    }

    /**
     * Add a recipient to the message.
     *
     * @param  string $address
     * @param  string|null $name
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function to($address, $name = null)
    {
        if (!is_string($address)) {
            throw new \InvalidArgumentException('Address must be a string.');
        }
        if (!is_null($name) && !is_string($name)) {
            throw new \InvalidArgumentException('Name must be a string.');
        }

        try {
            $this->swiftMessage->addTo($address, $name);
        } catch(\Swift_RfcComplianceException $e) {
            throw new \InvalidArgumentException($e);
        }

        return $this;
    }

    /**
     * Add a "from" address to the message.
     *
     * @param  string $address
     * @param  string|null $name
     *
     * @return  MessageInterface $this
     */
    public function from($address, $name = null)
    {
        $this->swiftMessage->addFrom($address, $name);

        return $this;
    }

    /**
     * Set the subject of the message.
     *
     * @param  string $subject
     *
     * @return  MessageInterface $this
     */
    public function subject($subject)
    {
        $this->swiftMessage->setSubject($subject);

        return $this;
    }

    /**
     * Attach a file to the message.
     *
     * @param  string $file
     * @param  array $options
     * 
     * @return MessageInterface $this
     * @throws PSMailException
     */
    public function attach($file, array $options = [])
    {
        if (!is_file($file)) {
            throw new PSMailException('File does not exists: ' . $file);
        }

        parent::attach($file, $options);

        if (isset($options['mime_type'])) {
            $attachment = \Swift_Attachment::fromPath($file, $options['mime_type']);
        } else {
            $attachment = \Swift_Attachment::fromPath($file);
        }

        if (isset($options['name'])) {
            $attachment->setFilename($options['name']);
        }

        $this->swiftMessage->attach($attachment);

        return $this;
    }

    /**
     * Set the body of the message
     *
     * @param string $body
     * @param string $contentType optional
     * @param string $charset optional
     *
     * @return MessageInterface $this
     */
    public function body($body, $contentType = null, $charset = null)
    {
        $this->swiftMessage->setBody($body, $contentType, $charset);

        return $this;
    }

    /**
     * Set the character set of this entity.
     *
     * @param string $charset
     *
     * @return MessageInterface $this
     */
    public function charset($charset)
    {
        $this->swiftMessage->setCharset($charset);

        return $this;
    }

    /**
     * Set the Content-type of this message.
     *
     * @param string $type
     *
     * @return MessageInterface $this
     */
    public function contentType($type)
    {
        $this->swiftMessage->setContentType($type);

        return $this;
    }

    /**
     * Get this message as a complete string.
     *
     * @return string
     */
    public function toString()
    {
        return $this->swiftMessage->toString();
    }

    /**
     * Validate email address
     *
     * @param string $emailAddress
     *
     * @return bool
     */
    public static function validationEmail($emailAddress)
    {
        return \Swift_Validate::email($emailAddress);
    }
}