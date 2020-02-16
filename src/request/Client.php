<?php
/**
 * MIT License <https://opensource.org/licenses/mit>
 *
 * Copyright (c) 2015 Kerem Güneş
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
declare(strict_types=1);

namespace froq\http\request;

use froq\util\Util;

/**
 * Client.
 * @package froq\http\request
 * @object  froq\http\request\Client
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class Client
{
    /**
     * Ip.
     * @var ?string
     */
    private ?string $ip = null;

    /**
     * Locale.
     * @var ?string
     */
    private ?string $locale = null;

    /**
     * Language.
     * @var ?string
     */
    private ?string $language = null;

    /**
     * User agent.
     * @var ?string
     */
    private ?string $userAgent = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->ip = Util::getClientIp();

        $acceptLanguage = trim($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        if ($acceptLanguage != '') {
            $this->locale = substr(str_replace('-', '_', $acceptLanguage), 0, 5);
            $this->language = substr($acceptLanguage, 0, 2);
        }

        $userAgent = Util::getClientUserAgent();
        if ($userAgent != null) {
            $this->userAgent = substr($userAgent, 0, 250); // Far enough for safety.
        }
    }

    /**
     * Get ip.
     * @return ?string
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Get locale.
     * @return ?string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Get language.
     * @return ?string
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Get user agent.
     * @return ?string
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
