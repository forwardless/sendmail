<?php

namespace pyatakss\sendmail;

interface MessageInterface
{

const LINE_SEPARATOR = "\r\n";


const LINE_LENGTH = 70;

/**
* Returns a unique ID for this message.
*
* @return string
*/
public function getId();

/**
* @return array
*/
public function getTo();

/**
* @return array
*/
public function getFrom();

/**
* @return string
*/
public function getSubject();

/**
* @return string
*/
public function getBody();

/**
* @return array
*/
public function getAttach();

/**
* Get the character set of this message.
*
* @return string
*/
public function getCharset();

/**
* Get the Content-type of this message.
*
* @return string
*/
public function getContentType();

/**
* Add a recipient to the message.
*
* @param  string $address
* @param  string|null  $name
*
* @throws \InvalidArgumentException
*
* @return $this
*/
public function to($address, $name = null);

/**
* Add a "from" address to the message.
*
* @param  string      $address
* @param  string|null $name
*
* @return  MessageInterface $this
*/
public function from($address, $name = null);

/**
* Set the subject of the message.
*
* @param  string $subject
*
* @return  MessageInterface $this
*/
public function subject($subject);

/**
* Attach a file to the message.
*
* @param  string $file
* @param  array  $options
*
* @return MessageInterface $this
*/
public function attach($file, array $options = []);

/**
* Set the body of the message
*
* @param string $body
* @param string $contentType optional
* @param string $charset     optional
*
* @return MessageInterface $this
*/
public function body($body, $contentType = null, $charset = null);


/**
* Set the character set of this entity.
*
* @param string $charset
*
* @return MessageInterface $this
*/
public function charset($charset);

/**
* Set the Content-type of this message.
*
* @param string $type
*
* @return MessageInterface $this
*/
public function contentType($type);

/**
* Get this message as a complete string.
*
* @return string
*/
public function toString();

/**
* Validate email address
*
* @param string $emailAddress
*
* @return bool
*/
public static function validationEmail($emailAddress);
}