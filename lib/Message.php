<?php

namespace pyatakss\sendmail;

class Message implements MessageInterface
{
    private $id;
    private $boundary;
    private $altBoundary;

    private $to = [];
    private $from = [];
    private $subject;
    private $body;
    private $contentType;
    private $charset;

    private $attach = [];
    private $headers = '';
    private $message;

    public function __construct()
    {
        $this->id = md5(microtime()) . '@messagesend';
        $this->boundary = md5(microtime());
        $this->altBoundary = 'alt-' . md5(microtime() - rand(1, 50));
    }

    /**
     * Returns a unique ID for this message.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getIdAsString()
    {
        $id = 'Message-ID: <' . $this->getId() . '>' . self::LINE_SEPARATOR;

        return $id;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     *
     * @return string
     * @throws \Exception
     */
    public function getToAsString()
    {
        if (!($this instanceof SwiftMessageAdapter) && empty($this->to)) {
            ExceptionHandler::collect(__CLASS__, 'Recipient address does not specified', __FILE__, __LINE__);

            return false;
        }

        $to = '';
        foreach ($this->to as $address => $recipient) {
            if (!empty($recipient)) {
                $to .= '=?UTF-8?B?' . base64_encode($recipient) . '?= ';
            }
            $to .= '<' . $address . '>, ';
        }
        $to = rtrim($to, ', ');

        return $to;
    }

    /**
     * @return array
     */
    public function getFrom()
    {
        return $this->from;
    }

    public function getFromAsString()
    {
        if (!($this instanceof SwiftMessageAdapter) && empty($this->from)) {
            ExceptionHandler::collect(__CLASS__, 'Sender address does not specified', __FILE__, __LINE__);

            return false;
        }

        $from = '';
        foreach ($this->from as $address => $sender) {
            if (!empty($sender)) {
                $from .= "=?UTF-8?B?" . $sender . '?= ';
            }
            $from .= '<' . $address . '>, ';
        }
        $from = rtrim($from, ', ');

        return $from;
    }

    public function getFromForSmtp()
    {
        if (!($this instanceof SwiftMessageAdapter) && empty($this->from)) {
            ExceptionHandler::collect(__CLASS__, 'Sender address does not specified', __FILE__, __LINE__);

            return false;
        }

        $from = '';
        foreach ($this->from as $address => $sender) {
            if (!empty($sender)) {
                $from .= $sender . ' ';
            }
            $from .= '<' . $address . '>, ';
        }
        $from = rtrim($from, ', ');

        return $from;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    public function getSubjectAsString()
    {
        $subject = '=?UTF-8?B?' . base64_encode($this->subject) . '?=';

        return $subject;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getBodyAsString()
    {
        $message = '';
        $message .= $this->body;

        return $message;
    }

    /**
     * @return array
     */
    public function getAttach()
    {
        return $this->attach;
    }

    public function getAttachAsString()
    {
        $str = '';
        foreach ($this->attach as $file => $options) {
            $str .= "--{$this->boundary}" . self::LINE_SEPARATOR;
            $str .= "Content-Type: {$options['mime_type']}; name=\"{$options['name']}\"" . self::LINE_SEPARATOR;
            $str .= "Content-Disposition: attachment; filename={$options['filename']}" . self::LINE_SEPARATOR;
            $str .= "Content-Transfer-Encoding: base64" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
            $str .= chunk_split(base64_encode($file)) . self::LINE_SEPARATOR;
        }

        return $str;
    }

    /**
     * Get the character set of this message.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Get the Content-type of this message.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
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
            throw new \InvalidArgumentException('Address must be a string');
        }
        if (!is_null($name) && !is_string($name)) {
            throw new \InvalidArgumentException('Name must be a string');
        }
        if (!self::validationEmail($address)) {
            ExceptionHandler::collect(__CLASS__, 'Email address is not valid: ' . $address, __FILE__, __LINE__);
        }

        $address = strtolower(trim($address));
        $name = trim(preg_replace('/[\r\n]+/', '', $name));

        if (!array_key_exists($address, $this->to)) {
            $this->to[$address] = ($name) ?: '';
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
        if (!self::validationEmail($address)) {
            ExceptionHandler::collect(__CLASS__, 'Email address is not valid' . $address, __FILE__, __LINE__);
        }

        $address = strtolower(trim($address));
        $name = trim(preg_replace('/[\r\n]+/', '', $name));

        if (!array_key_exists($address, $this->to)) {
            $this->from[$address] = ($name) ?: '';
        }

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
        $this->subject = (string)$subject;

        return $this;
    }

    /**
     * Attach a file to the message.
     *
     * @param  string $file
     * @param  array $options
     * @return MessageInterface $this
     */
    public function attach($file, array $options = [])
    {
        if (!is_file($file)) {
            ExceptionHandler::collect(__CLASS__, 'File does not exists: ' . $file, __FILE__, __LINE__);

            return $this;
        }

        $filename = end(explode(DIRECTORY_SEPARATOR, $file));
        $options['filename'] = $filename;
        if (!isset($options['name']) || $options['name'] == '') {
            $options['name'] = $filename;
        }
        if (!isset($options['mime_type']) || $options['mime_type'] == '') {
            $options['mime_type'] = @(new \finfo)->file($file, FILEINFO_MIME);
        }

        $this->attach[$file] = $options;

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
        $this->body = (string)$body;

        if (!is_null($contentType)) {
            $this->contentType = $contentType;
        } else {
            $this->contentType = 'text/plain';
        }

        if (!is_null($charset)) {
            $this->charset = $charset;
        } else {
            $this->charset = 'utf-8';
        }

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
        $this->charset = $charset;

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
        $this->contentType = $type;

        return $this;
    }

    /**
     * Get this message as a complete string.
     *
     * @param string $mailer
     * @return string
     */
    public function toString($mailer = 'mail')
    {
        $id = $this->getIdAsString();
        if ($mailer === 'mail') {
            $from = 'From: ' . $this->getFromAsString() . self::LINE_SEPARATOR;
        } else {
            $from = 'From: ' . $this->getFromForSmtp() . self::LINE_SEPARATOR;
        }
        $to = 'To: ' . $this->getToAsString() . self::LINE_SEPARATOR;
        $subject = 'Subject: ' . $this->getSubjectAsString() . self::LINE_SEPARATOR;

        $body = '';
        $body .= 'Date: ' . $this->getDateAsString() . self::LINE_SEPARATOR;
        $body .= 'MIME-Version: 1.0' . self::LINE_SEPARATOR;

        if (!empty($this->attach)) {
            $body .= 'Content-Type: multipart/mixed; boundary="' . $this->boundary . '"' . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

            $body .= "--{$this->boundary}" . self::LINE_SEPARATOR;
            $body .= 'Content-Type: multipart/alternative; boundary="' . $this->altBoundary . '"' . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

            $body .= "--{$this->altBoundary}" . self::LINE_SEPARATOR;
            $body .= "Content-Type: {$this->contentType}; charset={$this->charset}" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
            $body .= $this->getBodyAsString() . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

            $body .= "--{$this->altBoundary}--" . self::LINE_SEPARATOR;

            foreach ($this->attach as $file => $options) {
                $body .= "--{$this->boundary}" . self::LINE_SEPARATOR;
                $body .= "Content-Type: {$options['mime_type']}; name=\"{$options['name']}\"" . self::LINE_SEPARATOR;
                $body .= "Content-Disposition: attachment; filename=\"{$options['filename']}\"" . self::LINE_SEPARATOR;
                $body .= "Content-Transfer-Encoding: base64" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

                $body .= $this->getFile($file) . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
            }

            $body .= "--{$this->boundary}--" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
        } else {
            $body .= "Content-Type: {$this->contentType}; charset={$this->charset}" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
            $body .= $this->getBodyAsString() . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
        }

        $message = $id . $from . $to . $subject . $body;

        return $message;
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
        return filter_var($emailAddress, FILTER_VALIDATE_EMAIL);
    }

    protected function setHeaders($header)
    {
        $this->headers .= $header;

        return $header;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getDateAsString()
    {
        return date(DATE_RFC2822);
    }

    public function preSend($mailer)
    {
        $this->headers = '';
        $this->message = '';
        $this->headers .= $this->getIdAsString();
        if ($mailer === 'mail') {
            $this->headers .= 'From: ' . $this->getFromAsString() . self::LINE_SEPARATOR;
        } else {
            $this->headers .= 'From: ' . $this->getFromForSmtp() . self::LINE_SEPARATOR;
        }
        $this->headers .= 'Date: ' . $this->getDateAsString() . self::LINE_SEPARATOR;
        $this->headers .= 'MIME-Version: 1.0' . self::LINE_SEPARATOR;

        if (!empty($this->attach) && $mailer !== 'mandrill') {
            $this->headers .= 'Content-Type: multipart/mixed; boundary="' . $this->boundary . '"' . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

            $this->message .= "--{$this->boundary}" . self::LINE_SEPARATOR;
            $this->message .= 'Content-Type: multipart/alternative; boundary="' . $this->altBoundary . '"' . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

            $this->message .= "--{$this->altBoundary}" . self::LINE_SEPARATOR;
            $this->message .= "Content-Type: {$this->contentType}; charset={$this->charset}" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

            $this->message .= $this->getBodyAsString() . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

            $this->message .= "--{$this->altBoundary}--" . self::LINE_SEPARATOR;

            foreach ($this->attach as $file => $options) {
                $this->message .= "--{$this->boundary}" . self::LINE_SEPARATOR;
                $this->message .= "Content-Type: {$options['mime_type']}; name=\"{$options['name']}\"" . self::LINE_SEPARATOR;
                $this->message .= "Content-Disposition: attachment; filename=\"{$options['filename']}\"" . self::LINE_SEPARATOR;
                $this->message .= "Content-Transfer-Encoding: base64" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;

                $this->message .= $this->getFile($file) . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
            }

            $this->message .= "--{$this->boundary}--" . self::LINE_SEPARATOR . self::LINE_SEPARATOR;
        } else {
            $this->headers .= "Content-Type: {$this->contentType}; charset={$this->charset}" . self::LINE_SEPARATOR;
            $this->message .= $this->getBodyAsString();
        }
    }

    public function getFile($file)
    {
        if (!is_file($file)) {
            ExceptionHandler::collect(__CLASS__, 'File does not exists: ' . $file, __FILE__, __LINE__);

            return null;
        }

        $handle = @fopen($file, 'rb');
        $f_contents = @fread($handle, @filesize($file));
        $f_contents = chunk_split(base64_encode($f_contents));
        fclose($handle);

        return $f_contents;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function word_chunk($str, $len = self::LINE_LENGTH, $end = "\n")
    {
        $pattern = '~.{1,' . $len . '}~u';
        $str = preg_replace($pattern, '$0' . $end, $str);

        return rtrim($str, $end);
    }
}