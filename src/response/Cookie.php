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

        if ($sameSite != null) {
            $sameSite = strtolower($sameSite);
        }

        $this->setData(compact(self::$components)); // Store.
    }

    /**
     * @inheritDoc froq\common\interface\Stringable
     */
    public function toString(): string
    {
        extract($this->getData()); // Unstore.

        $ret = rawurlencode($name) .'=';

        if ($value === null || $value === '' || $expires < 0) {
            $ret .= sprintf('n/a; Expires=%s; Max-Age=0', Http::date(0)); // Remove.
        } else {
            $ret .= rawurlencode($value);

            // Must be given in-seconds format.
            if ($expires !== null) {
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
