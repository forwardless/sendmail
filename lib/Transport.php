<?php

namespace pyatakss\sendmail;

class Transport
{
    protected $configuration;

    public function __construct($configuration = null)
    {
        $this->configuration = $configuration;
    }

    /**
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

    /**
     * @param $messageStr
     * @param bool $asString
     * @return array
     */
    protected function getTo($messageStr, $asString = false)
    {
        preg_match('/(?:.*?)To: (.*?)(?:\r\n)|(?:\n)|(?:\r)/s', $messageStr, $matches);
        if (isset($matches[1]) && !empty($matches[1]) && !$asString) {
            return $this->extractEmailsNames($matches[1]);
        } elseif (isset($matches[1]) && !empty($matches[1])) {
            $this->extractEmailsNames($matches[1]);
        }
    }

    /**
     * @param $messageStr
     * @param bool $asString
     * @return array
     */
    protected function getFrom($messageStr, $asString = false)
    {
        preg_match('/(?:.*?)From: (.*?)(?:\r\n)|(?:\n)|(?:\r)/s', $messageStr, $matches);
        if (isset($matches[1]) && !empty($matches[1]) && !$asString) {
            return $this->extractEmailsNames($matches[1]);
        } elseif (isset($matches[1]) && !empty($matches[1])) {
            $this->extractEmailsNames($matches[1]);
        }
    }
}