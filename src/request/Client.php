<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\util\Util;

/**
 * An accessor class, for accessing some client properties.
 *
 * @package froq\http\request
 * @object  froq\http\request\Client
 * @author  Kerem Güneş
 * @since   1.0
 */
final class Client
{
    /**
     * Constructor.
     */
    public function __construct()
    {}

    /**
     * Get ip.
     *
     * @return ?string
     */
    public function getIp(): ?string
    {
        return Util::getClientIp();
    }

    /**
     * Get user agent.
     *
     * @return ?string
     */
    public function getUserAgent(): ?string
    {
        return Util::getClientUserAgent();
    }

    /**
     * Get locale.
     *
     * @return ?string
     */
    public function getLocale(): ?string
    {
        $acceptLanguage = $this->getAcceptLanguage();

        if ($acceptLanguage) {
            $language = substr($acceptLanguage, 0, 2);

            if (str_contains($acceptLanguage, '-')) {
                $temp = split('-', substr($acceptLanguage, 0, 5), 2);
                return sprintf('%s_%s', $temp[0], strtoupper($temp[1] ?: $temp[0]));
            } else {
                return sprintf('%s_%s', $language, strtoupper($language));
            }
        }

        return null;
    }

    /**
     * Get language.
     *
     * @return ?string
     */
    public function getLanguage(): ?string
    {
        $acceptLanguage = $this->getAcceptLanguage();

        if ($acceptLanguage) {
            return substr($acceptLanguage, 0, 2);
        }
        return null;
    }

    /**
     * Get accept-language.
     *
     * @return ?string
     * @since  6.0
     */
    public function getAcceptLanguage(): ?string
    {
        return $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
    }

    /**
     * Get referer.
     *
     * @return ?string
     * @since  6.0
     */
    public function getReferer(): ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }
}
