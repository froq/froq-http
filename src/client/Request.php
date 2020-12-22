<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\client;

use froq\http\client\Message;

/**
 * Request.
 *
 * @package froq\http\client
 * @object  froq\http\client\Request
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0
 */
final class Request extends Message
{
    /** @var string */
    private string $method;

    /** @var string */
    private string $url;

    /** @var ?array */
    private ?array $urlParams = null;

    /**
     * Constructor.
     *
     * @param string      $method
     * @param string      $url
     * @param array|null  $urlParams
     * @param string|null $body
     * @param array|null  $headers
     */
    public function __construct(string $method, string $url, array $urlParams = null, string $body = null,
        array $headers = null)
    {
        $this->setMethod($method)
             ->setUrl($url)
             ->setUrlParams($urlParams);

        // Default headers.
        static $headersDefault = [
            'accept' => '*/*',
            'accept-encoding' => 'gzip',
            'user-agent' => 'Froq HTTP Client (+https://github.com/froq/froq-http)',
        ];

        // Merge & normalize headers.
        $headers = array_replace_recursive($headersDefault, $headers ?? []);
        $headers = array_change_key_case($headers, CASE_LOWER);

        parent::__construct(Message::TYPE_REQUEST, null, $headers, $body);
    }

    /**
     * Set method.
     *
     * @param  string $method
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Get method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set url.
     *
     * @param  string $url
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set url params.
     *
     * @param  array|null $urlParams
     * @return self
     */
    public function setUrlParams(array|null $urlParams): self
    {
        $this->urlParams = $urlParams;

        return $this;
    }

    /**
     * Get url params.
     *
     * @return array|null
     */
    public function getUrlParams(): array|null
    {
        return $this->urlParams;
    }

    /**
     * Get uri.
     *
     * @return string
     * @internal
     */
    protected function getUri(): string
    {
        // Extract the only path and query part of URL.
        return preg_replace('~^\w+://[^/]+(/.*)~', '\\1', $this->getUrl());
    }
}
