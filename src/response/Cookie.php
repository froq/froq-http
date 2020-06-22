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

namespace froq\http\response;

use froq\common\interfaces\Stringable;
use froq\collection\ComponentCollection;
use froq\util\Arrays;
use froq\http\Http;
use froq\http\common\CookieException;

/**
 * Cookie.
 * @package froq\http\response
 * @object  froq\http\response\Cookie
 * @author  Kerem Güneş <k-gun@mail.com>
 */
final class Cookie extends ComponentCollection implements Stringable
{
    /**
     * Special chars.
     * @var string
     */
    private static string $specialChars = "=,; \t\r\n\v\f";

    /**
     * Same site values.
     * @var array<string>
     */
    private static array $sameSiteValues = ['None', 'Lax', 'Strict'];

    /**
     * Name pattern.
     * @var string
     */
    private static string $namePattern = '[\w][\w\-\.]*';

    /**
     * Constructor.
     * @param  string     $name
     * @param  ?scalar    $value
     * @param  array|null $options
     * @throws froq\http\common\CookieException
     */
    public function __construct(string $name, $value, array $options = null)
    {
        static $components = ['name', 'value', 'expires', 'path', 'domain', 'secure', 'httpOnly',
            'sameSite'];

        // Set components.
        parent::__construct($components);

        // Check name.
        if (!preg_match('~^'. self::$namePattern .'$~', $name)) {
            throw new CookieException('Invalid cookie name "%s", a valid name pattern is "%s"',
                [$name, self::$namePattern]);
        }

        if ($value != null && !is_scalar($value)) {
            throw new CookieException('Invalid value type "%s", scalar or null values accepted only',
                [gettype($value)]);
        }

        $options = ['name' => $name, 'value' => $value] + ($options ?? []);

        // Fix case issues.
        $options = array_change_key_case($options);
        Arrays::swap($options, 'httponly', 'httpOnly');
        Arrays::swap($options, 'samesite', 'sameSite');

        foreach ($options as $name => $value) {
            $this->set($name, $value);
        }

        // Define defaults for component names.
        $expires = $path = $domain = $secure = $httpOnly = $sameSite = null;

        extract($options);

        if ($sameSite != '') {
            $sameSite = ucfirst(strtolower($sameSite));
            if (!in_array($sameSite, self::$sameSiteValues)) {
                throw new CookieException(sprintf('Invalid sameSite value "%s", valids are: %s',
                    $sameSite, join(', ', self::$sameSiteValues)));
            }
        }

        $this->setData(compact($components)); // Store.
    }

    /**
     * @inheritDoc froq\common\interfaces\Stringable
     */
    public function toString(): string
    {
        extract($this->getData()); // Unstore.

        $ret = $name .'=';
        if ($value === null || $expires < 0) {
            $ret .= sprintf('n/a; Expires=%s; Max-Age=0', Http::date(0));
        } else {
            // String, bool, int or float.
            switch (gettype($value)) {
                case 'string':
                    $ret .= strpbrk($value, self::$specialChars) ? rawurlencode($value) : $value;
                    break;
                case 'boolean':
                    $ret .= $value ? 'true' : 'false';
                    break;
                default:
                    $ret .= strval($value);
            }

            // Must be given in-seconds format.
            if ($expires != null) {
                $ret .= sprintf('; Expires=%s; Max-Age=%s', Http::date(time() + $expires), $expires);
            }
        }

        $path     && $ret .= '; Path='. $path;
        $domain   && $ret .= '; Domain='. $domain;
        $secure   && $ret .= '; Secure';
        $httpOnly && $ret .= '; HttpOnly';
        $sameSite && $ret .= '; SameSite='. $sameSite;

        return $ret;
    }
}
