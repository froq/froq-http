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

use Froq\Util\Util;
use Froq\Util\Traits\GetterTrait;

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Client
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Client
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use GetterTrait;

    /**
     * Client IP.
     * @var string
     */
    private $ip;

    /**
     * Client locale.
     * @var string
     */
    private $locale;

    /**
     * Client language.
     * @var string
     */
    private $language;

    /**
     * Constructor.
     */
    final public function __construct()
    {
        // set ip
        $this->ip = Util::getClientIp();

        // set locale & language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->locale = str_replace('-', '_',
                substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5));
            $this->language = substr($this->locale, 0, 2);
        }
    }
}
