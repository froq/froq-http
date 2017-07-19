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
    private $statusCode;

    /**
     * Data.
     * @var any
     */
    private $data;

    /**
     * Data type.
     * @var string
     */
    private $dataType;

    /**
     * Data charset.
     * @var string
     */
    private $dataCharset;

    /**
     * Headers.
     * @var array
     */
    private $headers = [];

    /**
     * Cookies.
     * @var array
     */
    private $cookies = [];

    /**
     * Constructor.
     * @param int        $statusCode
     * @param any        $data
     * @param string     $dataType
     * @param array|null $headers
     * @param array|null $cookies
     */
    final public function __construct(int $statusCode, $data = null,
        $dataType = null, array $headers = null, array $cookies = null)
    {
        $this->statusCode = $statusCode;

        $this->data = $data;
        if ($dataType) {
            if (is_array($dataType)) {
                $this->dataType = $dataType[0];
                $this->dataCharset = $dataType[1] ?? Body::CONTENT_CHARSET_UTF8;
            } else {
                $this->dataType = $dataType;
                $this->dataCharset = Body::CONTENT_CHARSET_UTF8;
            }
        }

        $this->headers = (array) $headers;
        $this->cookies = (array) $cookies;
    }

    /**
     * Get status code.
     * @return int
     */
    final public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get data.
     * @return any
     */
    final public function getData()
    {
        return $this->data;
    }

    /**
     * Get data type.
     * @return string
     */
    final public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * Get data charset.
     * @return string
     */
    final public function getDataCharset(): string
    {
        return $this->dataCharset;
    }

    /**
     * Get headers.
     * @return array
     */
    final public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get cookies.
     * @return array
     */
    final public function getCookies(): array
    {
        return $this->cookies;
    }
}
