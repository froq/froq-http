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

use froq\util\Arrays;
use froq\common\interfaces\Stringable;
use froq\collection\ComponentCollection;
use froq\http\Http;
use froq\http\common\exceptions\CookieException;

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
    private static $specialChars = "=,; \t\r\n\v\f";

    /**
     * Same site values.
     * @var array
     */
    private static $sameSiteValues = ['None', 'Lax', 'Strict'];

    /**
     * Constructor.
     * @param  string      $name
     * @param  ?scalar     $value
     * @param  array|null  $options
     * @throws froq\http\common\exceptions\CookieException
     */
    public function __construct(string $name, $value, array $options = null)
    {
        // Set component names.
        parent::__construct(['name', 'value', 'expires', 'path', 'domain', 'secure', 'httpOnly',
            'sameSite']);

        // Check name.
        if (!preg_match('~^[\w][\w\-\.]*$~', $name)) {
            throw new CookieException(sprintf('Invalid cookie name "%s", a valid name pattern is '.
                '"[\w][\w\-\.]*"', $name));
        }

        if ($value != null && !is_scalar($value)) {
            throw new CookieException(sprintf('Invalid value type "%s", scalar or null '.
                'values accepted only', gettype($value)));
        }

        $expires = $path = $domain = $secure = $httpOnly = $sameSite = null;
        if ($options != null) {
            @ ['expires' => $expires, 'path'     => $path,     'domain'   => $domain,
               'secure'  => $secure,  'httpOnly' => $httpOnly, 'sameSite' => $sameSite] = $options;
        }

        if ($sameSite != '') {
            $sameSite = ucfirst(strtolower($sameSite));
            if (!in_array($sameSite, self::$sameSiteValues)) {
                throw new CookieException(sprintf('Invalid samesite value "%s", valids are "%s"',
                    $sameSite, join(', ', self::$sameSiteValues)));
            }
        }

        foreach ([
            'name'    => $name,    'value'    => $value,
            'expires' => $expires, 'path'     => $path,     'domain'   => $domain,
            'secure'  => $secure,  'httpOnly' => $httpOnly, 'sameSite' => $sameSite
        ] as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * @inheritDoc froq\common\interfaces\Stringable
     */
    public function toString(): string
    {
        $ret = '';

        @ ['name'    => $name,    'value'    => $value,
           'expires' => $expires, 'path'     => $path,     'domain'   => $domain,
           'secure'  => $secure,  'httpOnly' => $httpOnly, 'sameSite' => $sameSite
          ] = $this->toArray();

        $ret = $name .'=';
        if ($value === null || $expires < 0) {
            $ret .= 'NULL; Expires='. Http::date(0) .'; Max-Age=0';
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
                    $ret .= (string) $value;
            }

            if ($expires != null) {
                $ret .= '; Expires='. Http::date(time() + $expires) .'; '.
                    'Max-Age='. $expires;
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