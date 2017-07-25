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

use Froq\Encoding\{Json, JsonException};

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Response\ResponseJson
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class ResponseJson extends Response
{
    /**
     * Constructor.
     * @param int|null    $statusCode
     * @param any|null    $data
     * @param string|null $dataCharset
     * @param array|null  $headers
     * @param array|null  $cookies
     */
    public function __construct(int $statusCode = null, $data = null, string $dataCharset = null,
        array $headers = null, array $cookies = null)
    {
        if ($data) {
            $json = new Json($data);
            $data = $json->encode();
            if ($json->hasError()) {
                throw new JsonException($json->getErrorMessage(), $json->getErrorCode());
            }
        }

        $dataType = [Body::CONTENT_TYPE_APPLICATION_JSON, $dataCharset];

        parent::__construct($statusCode, $data, $dataType, $headers, $cookies);
    }
}
