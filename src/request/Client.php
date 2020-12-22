<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\util\Util;

/**
 * Client.
 *
 * @package froq\http\request
 * @object  froq\http\request\Client
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Client
{
    /** @var ?string */
    private ?string $ip = null;

    /** @var ?string */
    private ?string $locale = null;

    /** @var ?string */
    private ?string $language = null;

    /** @var ?string */
    private ?string $userAgent = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->ip = Util::getClientIp();

        $acceptLanguage = trim($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        if ($acceptLanguage != '') {
            $this->locale   = substr(str_replace('-', '_', $acceptLanguage), 0, 5);
            $this->language = substr($acceptLanguage, 0, 2);
        }

        $userAgent = Util::getClientUserAgent();
        if ($userAgent != null) {
            $this->userAgent = substr($userAgent, 0, 255); // Far enough for safety.
        }
    }

    /**
     * Get ip.
     *
     * @return ?string
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Get locale.
     *
     * @return ?string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Get language.
     *
     * @return ?string
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Get user agent.
     *
     * @return ?string
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
