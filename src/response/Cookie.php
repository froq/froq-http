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
use froq\inters\Stringable;
use froq\collection\ComponentCollection;
use froq\http\{Http, util\CookieException};

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
     * @param  scalar|null $value
     * @param  array|null  $options
     * @throws froq\http\util\CookieException
     */
    public function __construct(string $name, $value, array $options = null)
    {
        // Set component names.
        parent::__construct(['name', 'value', 'expires', 'path', 'domain', 'secure', 'httpOnly',
            'sameSite']);

        if ($value != null && !is_scalar($value)) {
            throw new CookieException(sprintf('Invalid value type %s given, scalar or null '.
                'values accepted only', gettype($value)));
        }

        $expires = $path = $domain = $secure = $httpOnly = $sameSite = null;
        if ($options != null) {
            // Sequential/associative options.
            if (array_key_exists(0, $options)) {
                @ [$expires, $path, $domain, $secure, $httpOnly, $sameSite] = $options;
            } else {
                @ ['expires' => $expires, 'path'     => $path,     'domain'   => $domain,
                   'secure'  => $secure,  'httpOnly' => $httpOnly, 'sameSite' => $sameSite
                  ] = $options;
            }
        }

        if ($sameSite != '') {
            $sameSite = ucfirst(strtolower((string) $sameSite));
            if (!in_array($sameSite, self::$sameSiteValues)) {
                throw new CookieException(sprintf('Invalid samesite value %s, valid values are '.
                    '%s', $sameSite, join(', ', self::$sameSiteValues)));
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
     * Create.
     * @param ...$arguments
     * @return froq\http\response\Cookie
     */
    public static function create(...$arguments): Cookie
    {
        return new Cookie(...$arguments);
    }

    /**
     * Create from options.
     * @param  string $name
     * @param  array  $options
     * @return froq\http\response\Cookie
     */
    public static function createFromOptions(string $name, array $options): Cookie
    {
        return new Cookie($name, ...self::exportValueAndOptions($options));
    }

    /**
     * Export value and options.
     * @param  array $input
     * @return array
     */
    public static function exportValueAndOptions(array $input): array
    {
        $value = null;
        $options = $input;

        if (array_key_exists(0, $options)) {
            $value = Arrays::pull($options, 0);
            $options = array_values($options); // Fix indexes.
        } elseif (array_key_exists('value', $options)) {
            $value = Arrays::pull($options, 'value');
        }

        return [$value, $options];
    }

    /**
     * @inheritDoc froq\inters\Stringable
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

        if ($path != '')     $ret .= '; Path='. $path;
        if ($domain != '')   $ret .= '; Domain='. $domain;
        if ($secure)         $ret .= '; Secure';
        if ($httpOnly)       $ret .= '; HttpOnly';
        if ($sameSite != '') $ret .= '; SameSite='. $sameSite;

        return $ret;
    }
}
