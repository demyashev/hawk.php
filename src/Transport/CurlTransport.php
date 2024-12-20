<?php

declare(strict_types=1);

namespace Hawk\Transport;

use Hawk\Event;

/**
 * Class CurlTransport is a transport object
 *
 * @package Hawk\Transport
 */
class CurlTransport implements TransportInterface
{
    /**
     * URL to send occurred event
     *
     * @var string
     */
    private $url;

    /**
     * CURLOPT_TIMEOUT
     *
     * @var int
     */
    private $timeout;

    /**
     * CurlTransport constructor.
     *
     * @param string $url
     */
    public function __construct(string $url, int $timeout)
    {
        $this->url = $url;
        $this->timeout = $timeout;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function send(Event $event)
    {
        /**
         * If php-curl is not available then throw an exception
         */
        if (!extension_loaded('curl')) {
            throw new \Exception('The cURL PHP extension is required to use the Hawk PHP Catcher');
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($event, JSON_UNESCAPED_UNICODE));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
