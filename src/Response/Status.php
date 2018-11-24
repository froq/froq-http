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

namespace Froq\Http\Response;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Response\Status
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Status
{
    /**
     * Informational constants.
     * @const int
     */
    public const CONTINUE                             = 100,
                 SWITCHING_PROTOCOLS                  = 101,
                 PROCESSING                           = 102;

    /**
     * Success constants.
     * @const int
     */
    public const OK                                   = 200,
                 CREATED                              = 201,
                 ACCEPTED                             = 202,
                 NON_AUTHORITATIVE_INFORMATION        = 203,
                 NO_CONTENT                           = 204,
                 RESET_CONTENT                        = 205,
                 PARTIAL_CONTENT                      = 206,
                 MULTI_STATUS                         = 207,
                 ALREADY_REPORTED                     = 208,
                 IM_USED                              = 226;

    /**
     * Redirection constants.
     * @const int
     */
    public const MULTIPLE_CHOICES                     = 300,
                 MOVED_PERMANENTLY                    = 301,
                 FOUND                                = 302,
                 SEE_OTHER                            = 303,
                 NOT_MODIFIED                         = 304,
                 USE_PROXY                            = 305,
                 TEMPORARY_REDIRECT                   = 307,
                 PERMANENT_REDIRECT                   = 308;

    /**
     * Client error constants.
     * @const int
     */
    public const BAD_REQUEST                          = 400,
                 UNAUTHORIZED                         = 401,
                 PAYMENT_REQUIRED                     = 402,
                 FORBIDDEN                            = 403,
                 NOT_FOUND                            = 404,
                 METHOD_NOT_ALLOWED                   = 405,
                 NOT_ACCEPTABLE                       = 406,
                 PROXY_AUTHENTICATION_REQUIRED        = 407,
                 REQUEST_TIMEOUT                      = 408,
                 CONFLICT                             = 409,
                 GONE                                 = 410,
                 LENGTH_REQUIRED                      = 411,
                 PRECONDITION_FAILED                  = 412,
                 PAYLOAD_TOO_LARGE                    = 413,
                 REQUEST_URI_TOO_LONG                 = 414,
                 UNSUPPORTED_MEDIA_TYPE               = 415,
                 REQUESTED_RANGE_NOT_SATISFIABLE      = 416,
                 EXPECTATION_FAILED                   = 417,
                 I_M_A_TEAPOT                         = 418,
                 MISDIRECTED_REQUEST                  = 421,
                 UNPROCESSABLE_ENTITY                 = 422,
                 LOCKED                               = 423,
                 FAILED_DEPENDENCY                    = 424,
                 UPGRADE_REQUIRED                     = 426,
                 PRECONDITION_REQUIRED                = 428,
                 TOO_MANY_REQUESTS                    = 429,
                 REQUEST_HEADER_FIELDS_TOO_LARGE      = 431,
                 CONNECTION_CLOSED_WITHOUT_RESPONSE   = 444,
                 UNAVAILABLE_FOR_LEGAL_REASONS        = 451,
                 CLIENT_CLOSED_REQUEST                = 499;

    /**
     * Server error constants.
     * @const int
     */
    public const INTERNAL_SERVER_ERROR                = 500,
                 NOT_IMPLEMENTED                      = 501,
                 BAD_GATEWAY                          = 502,
                 SERVICE_UNAVAILABLE                  = 503,
                 GATEWAY_TIMEOUT                      = 504,
                 HTTP_VERSION_NOT_SUPPORTED           = 505,
                 VARIANT_ALSO_NEGOTIATES              = 506,
                 INSUFFICIENT_STORAGE                 = 507,
                 LOOP_DETECTED                        = 508,
                 NOT_EXTENDED                         = 510,
                 NETWORK_AUTHENTICATION_REQUIRED      = 511,
                 NETWORK_CONNECT_TIMEOUT_ERROR        = 599;

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
        308 => 'Permanent Redirect',

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
        413 => 'Payload Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a Teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',

        // server error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error'
    ];

    /**
     * Constructor.
     * @param int    $code
     * @param string $text
     */
    public function __construct(int $code = self::OK, string $text = null)
    {
        if ($text == null) {
            $text = self::getTextByCode($code);
        }

        $this->code = $code;
        $this->text = $text;
    }

    /**
     * String magic.
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Set code.
     * @param  int $code
     * @return self
     */
    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Set text.
     * @param  string $text
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     * @param  int $code
     * @return ?string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * To string.
     * @return string
     */
    public function toString(): string
    {
        return sprintf('%s %s', $this->code, $this->text);
    }

    /**
     * Get statuses.
     * @return array
     */
    public static function getStatuses(): array
    {
        return self::$statuses;
    }

    /**
     * Get code by text.
     * @param  string $text
     * @return ?int
     */
    public static function getCodeByText(string $text): ?int
    {
        return array_flip(self::$statuses)[$text] ?? null;
    }

    /**
     * Get text by code.
     * @param  int $code
     * @return ?string
     */
    public static function getTextByCode(int $code): ?string
    {
        return self::$statuses[$code] ?? null;
    }
}
