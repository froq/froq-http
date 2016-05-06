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

namespace Froq\Http;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Http
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Http
{
    /**
     * Versions.
     * @const string
     */
    const VERSION_1_0       = 'HTTP/1.0',
          VERSION_1_1       = 'HTTP/1.1',
          VERSION_2_0       = 'HTTP/2.0',
          VERSION_LATEST    = self::VERSION_2_0,
          VERSION_CURRENT   = self::VERSION_1_1,
          VERSION_DEFAULT   = self::VERSION_CURRENT;

    /**
     * Methods.
     * @conts string
     */
    const METHOD_GET        = 'GET',
          METHOD_POST       = 'POST',
          METHOD_PUT        = 'PUT',
          METHOD_PATCH      = 'PATCH',
          METHOD_DELETE     = 'DELETE',
          METHOD_OPTIONS    = 'OPTIONS',
          METHOD_HEAD       = 'HEAD',
          METHOD_TRACE      = 'TRACE',
          METHOD_CONNECT    = 'CONNECT',
          // non-standard
          METHOD_COPY       = 'COPY',
          METHOD_MOVE       = 'MOVE';


    /**
     * Detect version.
     * @return string
     */
    final public static function detectVersion(): string
    {
        return ($_SERVER['SERVER_PROTOCOL'] ?? self::VERSION_DEFAULT);
    }
}
