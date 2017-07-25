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
