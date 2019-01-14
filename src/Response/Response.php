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

namespace Froq\Http\Response;

use Froq\Http\HttpException;

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
     * @param  int|string|array|null $_
     * @param  any|null              $data
     * @param  string|array          $dataType
     * @param  array|null            $headers
     * @param  array|null            $cookies
     * @throws Froq\Http\HttpException
     */
    public function __construct($_ = null, $data = null, $dataType = null,
        array $headers = null, array $cookies = null)
    {
        if ($_ !== null) {
            switch (gettype($_)) {
                // simply set status code
                case 'integer':
                    $statusCode = $_;
                    break;
                // this makes status default: 200 OK
                case 'string':
                    $data = $_;
                    break;
                // this overrides all arguments
                case 'array':
                    $_['statusCode'] = $_['code'] ?? null;
                    extract($_);
                    break;
                default:
                    throw new HttpException('Only int,string,array types and null '.
                        'accepted for first argument');
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
     * @return ?any
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
