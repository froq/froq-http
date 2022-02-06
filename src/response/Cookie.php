<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\response;

use froq\http\{Http, common\CookieException};
use froq\collection\ComponentCollection;
use froq\common\interface\Stringable;
use froq\util\Arrays;
use Assert;

/**
 * Cookie.
 *
 * @package froq\http\response
 * @object  froq\http\response\Cookie
 * @author  Kerem Güneş
 */
final class Cookie extends ComponentCollection implements Stringable
{
    /** @var array */
    private static array $components = ['name', 'value', 'expires', 'path', 'domain', 'secure',
        'httpOnly', 'sameSite'];

    /**
     * Constructor.
     *
     * @param  string      $name
     * @param  string|null $value
     * @param  array|null  $options
     * @throws froq\http\common\CookieException
     */
    public function __construct(string $name, string|null $value, array $options = null)
    {
        // Set components.
        parent::__construct(self::$components);

        // Prepare & validate name.
        $name = trim($name);
        Assert::regExp($name, '~^[\w][\w\.\-]*$~', new CookieException(
            'Invalid cookie name, it must be alphanumeric & non-empty string'
        ));

        $options = ['name' => $name, 'value' => $value] + ((array) $options);
        $options = Arrays::map($options, fn($o) => isset($o) ? strval($o) : $o);

        // Fix case issues.
        $options = Arrays::lowerKeys($options);
        Arrays::swap($options, 'httponly', 'httpOnly');
        Arrays::swap($options, 'samesite', 'sameSite');

        // Define defaults for component names.
        $expires = $path = $domain = $secure = $httpOnly = $sameSite = null;

        extract($options);

        $path     && $path     = trim($path);
        $secure   && $secure   = (bool) $secure;
        $httpOnly && $httpOnly = (bool) $httpOnly;
        $sameSite && $sameSite = trim($sameSite);

        if ($sameSite !== '') {
            // Valids: none, lax, strict, none; secure (@see https://web.dev/schemeful-samesite/).
            Assert::regExp($sameSite, '~^(?:(none|lax|strict|none; *secure))$~i', new CookieException(
                'Invalid samesite option `%s`', $sameSite
            ));

            // Formatify.
            $sameSite = xstring($sameSite)->splitMap('; *')->mapMulti('lower|ucfirst')->join('; ');
        }

        // Store.
        $this->data = compact(self::$components);
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        // Unstore.
        extract($this->data);

        $ret = rawurlencode($name) . '=';

        // Remove.
        if ($value === null || $value === '' || $expires < 0) {
            $ret .= sprintf('n/a; Expires=%s; Max-Age=0', Http::date(0));
        } else {
            $ret .= rawurlencode($value);

            // Must be given in-seconds format.
            if ($expires !== null) {
                $ret .= sprintf('; Expires=%s; Max-Age=%s', Http::date(time() + $expires), $expires);
            }
        }

        $path     && $ret .= '; Path=' . $path;
        $domain   && $ret .= '; Domain=' . $domain;
        $secure   && $ret .= '; Secure';
        $httpOnly && $ret .= '; HttpOnly';
        $sameSite && $ret .= '; SameSite=' . $sameSite;

        return $ret;
    }
}
