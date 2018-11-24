<?php
/**
 * Copyright (c) 2015 Kerem Güneş
 *
 * MIT License <https://opensource.org/licenses/mit>
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

namespace Froq\Http\Request;

use Froq\Util\Util;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Request\Client
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Client
{
    /**
     * Ip.
     * @var string
     */
    private $ip;

    /**
     * Locale.
     * @var string
     */
    private $locale;

    /**
     * Language.
     * @var string
     */
    private $language;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->ip = Util::getClientIp();

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->locale = str_replace('-', '_', substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5));
            $this->language = substr($this->locale, 0, 2);
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
}
