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

use Froq\Util\Traits\GetterTrait as Getter;

/**
 * @package    Froq
 * @subpackage Froq\Http\Response
 * @object     Froq\Http\Response\Body
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Body
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use Getter;

    /**
     * Content.
     * @var string
     */
    private $content;

    /**
     * Constructor.
     * @param string|null $data
     * @param string      $type
     * @param string      $charset
     */
    final public function __construct(string $data = null,
        string $type = BodyContent::TYPE_HTML, string $charset = BodyContent::CHARSET_UTF8)
    {
        $this->setContent(new BodyContent($data, $type, $charset));
    }

    /**
     * Set content.
     * @param Froq\Http\Response\BodyContent $content
     */
    final public function setContent(BodyContent $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     * @return Froq\Http\Response\BodyContent
     */
    final public function getContent(): BodyContent
    {
        return $this->content;
    }
}
