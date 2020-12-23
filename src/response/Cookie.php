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

/**
 * Cookie.
 *
 * @package froq\http\response
 * @object  froq\http\response\Cookie
 * @author  Kerem Güneş
 */
final class Cookie extends ComponentCollection implements Stringable
{
    /** @var string */
    private static string $specialChars = "=,; \t\r\n\v\f";

    /** @var array<string> */
    private static array $sameSiteValues = ['none', 'lax', 'strict'];

    /** @var string */
    private static string $namePattern = '[\w][\w\-\.]*';

    /** @var array */
    private static array $components = ['name', 'value', 'expires', 'path', 'domain', 'secure',
        'httpOnly', 'sameSite'];

    /**
     * Constructor.
     *
     * @param  string      $name
     * @param  scalar|null $value
     * @param  array|null  $options
     * @throws froq\http\common\CookieException
     */
    public function __construct(string $name, $value, array $options = null)
    {
        // Set components.
        parent::__construct(self::$components);

        // Check name.
        if (!preg_match('~^'. self::$namePattern .'$~', $name)) {
            throw new CookieException('Invalid cookie name `%s`, a valid name pattern is `%s`',
                [$name, self::$namePattern]);
        }

        if ($value !== null && !is_scalar($value)) {
            throw new CookieException('Invalid value type `%s`, scalar or null values accepted only',
                get_type($value));
        }

        $options = ['name' => $name, 'value' => $value] + ($options ?? []);

        // Fix case issues.
        $options = array_change_key_case($options, CASE_LOWER);
        Arrays::swap($options, 'httponly', 'httpOnly');
        Arrays::swap($options, 'samesite', 'sameSite');

        foreach ($options as $name => $value) {
            $this->set($name, $value);
        }

        // Define defaults for component names.
        $expires = $path = $domain = $secure = $httpOnly = $sameSite = null;

        extract($options);

        if ($sameSite != '') {
            $sameSite = strtolower($sameSite);
            if (!in_array($sameSite, self::$sameSiteValues)) {
                throw new CookieException('Invalid `sameSite` value `%s`, valids are: %s',
                    [$sameSite, join(', ', self::$sameSiteValues)]);
            }
        }

        $this->setData(compact(self::$components)); // Store.
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        extract($this->getData()); // Unstore.

        $ret = $name .'=';

        if ($value === null || $expires < 0) {
            // Remove.
            $ret .= sprintf('n/a; Expires=%s; Max-Age=0', Http::date(0));
        } else {
            // String, bool, int or float.
            $ret .= match (get_type($value)) {
                'string' => rawurlencode($value),
                'bool'   => $value ? 'true' : 'false',
                default  => strval($value)
            };

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
