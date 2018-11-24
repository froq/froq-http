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
 * @object     Froq\Http\Response\Response
 * @author     Kerem Güneş <k-gun@mail.com>
 */
class Response
{
    /**
     * Status code.
     * @var int
     */
    protected $statusCode;

    /**
     * Data.
     * @var any
     */
    protected $data;

    /**
     * Data type.
     * @var string
     */
    protected $dataType;

    /**
     * Data charset.
     * @var string
     */
    protected $dataCharset;

    /**
     * Headers.
     * @var array
     */
    protected $headers = [];

    /**
     * Cookies.
     * @var array
     */
    protected $cookies = [];

    /**
     * Constructor.
     * @param int|string|array|null $arg0
     * @param any|null              $data
     * @param string|array          $dataType
     * @param array|null            $headers
     * @param array|null            $cookies
     */
    public function __construct($arg0 = null, $data = null, $dataType = null,
        array $headers = null, array $cookies = null)
    {
        if ($arg0 !== null) {
            switch (gettype($arg0)) {
                // simply set status code
                case 'integer':
                    $statusCode = $arg0;
                    break;
                // this makes status default: 200 OK
                case 'string':
                    $data = $arg0;
                    break;
                // this overwrites all arguments
                case 'array':
                    $arg0['statusCode'] = $arg0['code'] ?? null;
                    extract($arg0);
                    break;
                default:
                    throw new \InvalidArgumentException(
                        'Only int|string|array types and null accepted for first argument!');
            }
        }

        $this->statusCode = $statusCode ?? Status::OK;

        $this->data = $data;
        if (!empty($dataType)) {
            if (is_array($dataType)) {
                $this->dataType = $dataType[0];
                $this->dataCharset = $dataType[1] ?? $dataCharset ?? null;
            } else {
                $this->dataType = $dataType;
                $this->dataCharset = $dataCharset ?? null;
            }
        }

        $this->headers = (array) $headers;
        $this->cookies = (array) $cookies;
    }

    /**
     * Get status code.
     * @return ?int
     */
    public final function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get data.
     * @return any
     */
    public final function getData()
    {
        return $this->data;
    }

    /**
     * Get data type.
     * @return ?string
     */
    public final function getDataType(): ?string
    {
        return $this->dataType;
    }

    /**
     * Get data charset.
     * @return ?string
     */
    public final function getDataCharset(): ?string
    {
        return $this->dataCharset;
    }

    /**
     * Get headers.
     * @return array
     */
    public final function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get cookies.
     * @return array
     */
    public final function getCookies(): array
    {
        return $this->cookies;
    }
}
