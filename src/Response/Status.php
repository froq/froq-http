<?php
/**
 * Copyright (c) 2016 Kerem Güneş
 *     <k-gun@mail.com>
 *
 * GNU General Public License v3.0
 *     <http://www.gnu.org/licenses/gpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Froq\Http\Response;

/**
 * @package    Froq
 * @subpackage Froq\Http\Response
 * @object     Froq\Http\Response\Status
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Status
{
    /**
     * Default code.
     * @const int
     */
    const DEFAULT_CODE = 200;

    /**
     * Default text.
     * @const string
     */
    const DEFAULT_TEXT = 'OK';

    /**
     * Informational constants.
     * @const int
     */
    const CONTINUE                         = 100,
          SWITCHING_PROTOCOLS              = 101,
          PROCESSING                       = 102;

    /**
     * Success constants.
     * @const int
     */
    const OK                               = 200,
          CREATED                          = 201,
          ACCEPTED                         = 202,
          NON_AUTHORITATIVE_INFORMATION    = 203,
          NO_CONTENT                       = 204,
          RESET_CONTENT                    = 205,
          PARTIAL_CONTENT                  = 206,
          MULTI_STATUS                     = 207,
          ALREADY_REPORTED                 = 208,
          IM_USED                          = 226;

    /**
     * Redirection constants.
     * @const int
     */
    const MULTIPLE_CHOICES                 = 300,
          MOVED_PERMANENTLY                = 301,
          FOUND                            = 302,
          SEE_OTHER                        = 303,
          NOT_MODIFIED                     = 304,
          USE_PROXY                        = 305,
          TEMPORARY_REDIRECT               = 307,
          PERMANENT_REDIRECT               = 308;

    /**
     * Client error constants.
     * @const int
     */
    const BAD_REQUEST                      = 400,
          UNAUTHORIZED                     = 401,
          PAYMENT_REQUIRED                 = 402,
          FORBIDDEN                        = 403,
          NOT_FOUND                        = 404,
          METHOD_NOT_ALLOWED               = 405,
          NOT_ACCEPTABLE                   = 406,
          PROXY_AUTHENTICATION_REQUIRED    = 407,
          REQUEST_TIMEOUT                  = 408,
          CONFLICT                         = 409,
          GONE                             = 410,
          LENGTH_REQUIRED                  = 411,
          PRECONDITION_FAILED              = 412,
          REQUEST_ENTITY_TOO_LARGE         = 413,
          REQUEST_URI_TOO_LONG             = 414,
          UNSUPPORTED_MEDIA_TYPE           = 415,
          REQUESTED_RANGE_NOT_SATISFIABLE  = 416,
          EXPECTATION_FAILED               = 417,
          I_M_A_TEAPOT                     = 418;

    /**
     * Server error constants.
     * @const int
     */
    const INTERNAL_SERVER_ERROR            = 500,
          NOT_IMPLEMENTED                  = 501,
          BAD_GATEWAY                      = 502,
          SERVICE_UNAVAILABLE              = 503,
          GATEWAY_TIMEOUT                  = 504,
          HTTP_VERSION_NOT_SUPPORTED       = 505,
          BANDWIDTH_LIMIT_EXCEEDED         = 509;

    /**
     * Code.
     * @var int
     */
    private $code;

    /**
     * Text.
     * @var string
     */
    private $text;

    /**
     * Statuses.
     * @var array
     */
    private static $statuses = [
        // informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        // success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // client error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'a a Teapot',

        // server error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded',
    ];

    /**
     * Constructor.
     * @param int    $code
     * @param string $text
     */
    final public function __construct(int $code = self::DEFAULT_CODE, string $text = null)
    {
        if ($text == '') {
            $text = self::getTextByCode($code);
        }

        $this->code = $code;
        $this->text = $text;
    }

    /**
     * Stringer.
     * @return string
     */
    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Set code.
     * @param  int $code
     * @return self
     */
    final public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     * @return int
     */
    final public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Set text.
     * @param  string $text
     * @return self
     */
    final public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     * @param  int $code
     * @return string
     */
    final public function getText(): string
    {
        return $this->text;
    }

    /**
     * To string.
     * @return string
     */
    final public function toString(): string
    {
        return sprintf('%s %s', $this->code, $this->text);
    }

    /**
     * Get statuses.
     * @return array
     */
    final public static function getStatuses(): array
    {
        return self::$statuses;
    }

    /**
     * Get code by text.
     * @param  string $text
     * @return int
     */
    final public static function getCodeByText(string $text): int
    {
        return array_flip(self::$statuses)[$text] ?? 0;
    }

    /**
     * Get text by code.
     * @param  int $code
     * @return string
     */
    final public static function getTextByCode(int $code): string
    {
        return self::$statuses[$code] ?? '';
    }
}
