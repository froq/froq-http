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

use Froq\Util\Traits\GetterTrait as Getter;
use Froq\Http\Response\{Status, Body, BodyContent};
use Froq\Encoding\{Gzip, GzipException, Json, JsonException};

/**
 * @package    Froq
 * @subpackage Froq\Http
 * @object     Froq\Http\Response
 * @author     Kerem Güneş <k-gun@mail.com>
 */
final class Response
{
    /**
     * Getter.
     * @object Froq\Util\Traits\GetterTrait
     */
    use Getter;

    /**
     * HTTP Version.
     * @var string
     */
    private $httpVersion;

    /**
     * Status object.
     * @var Froq\Http\Response\Status
     */
    private $status;

    /**
     * Body object.
     * @var Froq\Http\Response\Body
     */
    private $body;

    /**
     * Headers object.
     * @var Froq\Http\Headers
     */
    private $headers;

    /**
     * Cookies object.
     * @var Froq\Http\Cookies
     */
    private $cookies;

    /**
     * Gzip.
     * @var Froq\Encoding\Gzip
     */
    // private $gzip;

    /**
     * Gzip options.
     * @var array
     */
    // private $gzipOptions = [];

    /**
     * Constructor.
     * @param int      $status
     * @param any|null $body
     * @param array    $headers
     * @param array    $cookies
     */
    final public function __construct(int $status = Status::OK, $body = null,
        array $headers = [], array $cookies = [])
    {
        // set http version
        $this->httpVersion = ($_SERVER['SERVER_PROTOCOL'] ?? Http::VERSION_1_1);

        // set status
        $this->status = new Status($status);

        // set body
        $this->body = new Body();
    }

    /**
     * Set body.
     * @param  any $body
     * @return self
     */
    final public function setBody($body): self
    {
        switch ($this->body->content->getType()) {
            case BodyContent::TYPE_JSON:
                break;
        }

        $this->body->content->setData($body);
        $this->body->content->setLength(strlen($body));

        return $this;
    }

    /**
     * Send status, content type and body.
     * @return void
     */
    final public function send()
    {
        // send status
        header(sprintf('%s %s',
            $this->httpVersion, $this->status->toString()));

        // body stuff
        $bodyContentType    = $this->body->content->getType();
        $bodyContentCharset = $this->body->content->getCharset();
        $bodyContentLength  = $this->body->content->getLength();

        // content type / length
        // if (empty($bodyContentType)) {
        //     $this->sendHeader('Content-Type', Content::TYPE_NONE);
        // } elseif (empty($bodyContentCharset)
        //     || strtolower($bodyContentType) == Content::TYPE_NONE) {
        //         $this->sendHeader('Content-Type', $bodyContentType);
        // } else {
        //     $this->sendHeader('Content-Type', sprintf('%s; charset=%s',
        //         $bodyContentType, $bodyContentCharset));
        // }
        // $this->sendHeader('Content-Length', $bodyContentLength);

        // // real load time
        // $this->sendHeader('X-Load-Time', app()->loadTime());

        // print it beybe!
        print $this->body->content;
    }

    // @wait
    final public function sendFile($file) {}
}
