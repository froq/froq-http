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

/**
 * Status Codes.
 *
 * Respresents an HTTP Status Code registry with some utility methods. All code & text (reason
 * phrases) resouces could be found at: https://www.iana.org/assignments/http-status-codes/http-status-codes.txt
 *
 * @package froq\http\response
 * @object  froq\http\response\StatusCodes
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.0
 * @static
 */
class StatusCodes
{
    /**
     * Status constants.
     * @const int
     */
    public const
        // Informationals (1xx).
        CONTINUE                        = 100,
        SWITCHING_PROTOCOLS             = 101,
        PROCESSING                      = 102,
        EARLY_HINTS                     = 103,

        // Successes (2xx).
        OK                              = 200,
        CREATED                         = 201,
        ACCEPTED                        = 202,
        NON_AUTHORITATIVE_INFORMATION   = 203,
        NO_CONTENT                      = 204,
        RESET_CONTENT                   = 205,
        PARTIAL_CONTENT                 = 206,
        MULTI_STATUS                    = 207,
        ALREADY_REPORTED                = 208,
        IM_USED                         = 226,

        // Redirections (3xx).
        MULTIPLE_CHOICES                = 300,
        MOVED_PERMANENTLY               = 301,
        FOUND                           = 302,
        SEE_OTHER                       = 303,
        NOT_MODIFIED                    = 304,
        USE_PROXY                       = 305,
        TEMPORARY_REDIRECT              = 307,
        PERMANENT_REDIRECT              = 308,

        // Client errors (4xx).
        BAD_REQUEST                     = 400,
        UNAUTHORIZED                    = 401,
        PAYMENT_REQUIRED                = 402,
        FORBIDDEN                       = 403,
        NOT_FOUND                       = 404,
        METHOD_NOT_ALLOWED              = 405,
        NOT_ACCEPTABLE                  = 406,
        PROXY_AUTHENTICATION_REQUIRED   = 407,
        REQUEST_TIMEOUT                 = 408,
        CONFLICT                        = 409,
        GONE                            = 410,
        LENGTH_REQUIRED                 = 411,
        PRECONDITION_FAILED             = 412,
        PAYLOAD_TOO_LARGE               = 413,
        URI_TOO_LONG                    = 414,
        UNSUPPORTED_MEDIA_TYPE          = 415,
        RANGE_NOT_SATISFIABLE           = 416,
        EXPECTATION_FAILED              = 417,
        MISDIRECTED_REQUEST             = 421,
        UNPROCESSABLE_ENTITY            = 422,
        LOCKED                          = 423,
        FAILED_DEPENDENCY               = 424,
        TOO_EARLY                       = 425,
        UPGRADE_REQUIRED                = 426,
        PRECONDITION_REQUIRED           = 428,
        TOO_MANY_REQUESTS               = 429,
        REQUEST_HEADER_FIELDS_TOO_LARGE = 431,
        UNAVAILABLE_FOR_LEGAL_REASONS   = 451,

        // Server errors (5xx).
        INTERNAL_SERVER_ERROR           = 500,
        NOT_IMPLEMENTED                 = 501,
        BAD_GATEWAY                     = 502,
        SERVICE_UNAVAILABLE             = 503,
        GATEWAY_TIMEOUT                 = 504,
        HTTP_VERSION_NOT_SUPPORTED      = 505,
        VARIANT_ALSO_NEGOTIATES         = 506,
        INSUFFICIENT_STORAGE            = 507,
        LOOP_DETECTED                   = 508,
        NOT_EXTENDED                    = 510,
        NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * Statuses.
     * @var array
     */
    private static array $statuses = [
        // Informationals (1xx).
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        // Successes (2xx).
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        // Redirections (3xx).
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        // Client errors (4xx).
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
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        // Server errors (5xx).
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
    ];

    /**
     * All.
     * @return array
     */
    public static final function all(): array
    {
        return self::$statuses;
    }

    /**
     * Validate.
     * @param  int $code
     * @return bool
     */
    public static final function validate(int $code): bool
    {
        // Since only IANA-defined codes are here, do not use $statuses.
        // return array_key_exists($code, self::$statuses);

        return ($code >= 100 && $code <= 599);
    }

    /**
     * Get code by text.
     * @param  string $text
     * @return ?int
     */
    public static final function getCodeByText(string $text): ?int
    {
        return array_flip(self::$statuses)[$text] ?? null;
    }

    /**
     * Get text by code.
     * @param  int $code
     * @return ?string
     */
    public static final function getTextByCode(int $code): ?string
    {
        return self::$statuses[$code] ?? null;
    }
}
