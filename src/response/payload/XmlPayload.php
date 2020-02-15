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

namespace froq\http\response\payload;

use froq\encoding\Encoder;
use froq\http\Response as Container;
use froq\http\message\Body;
use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};

/**
 * Xml Payload.
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\XmlPayload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   4.0
 */
final class XmlPayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     * @param int                     $code
     * @param array|object|string     $content
     * @param array                   $attributes
     * @param froq\http\Response|null $container
     */
    public function __construct(int $code, $content, array $attributes = null,
        Container $container = null)
    {
        $attributes['type'] ??= Body::CONTENT_TYPE_APPLICATION_XML;

        parent::__construct($code, $content, $attributes, $container);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        $content = $this->getContent();

        if (!is_array($content)) {
            throw new PayloadException('Content must be "array" for XML payloads, "%s" given',
                [gettype($content)]);
        }

        $options = null;
        if ($this->container != null) {
            $options = $this->container->getApp()->config('response.xml');
        }

        if (!Encoder::isEncoded('xml', $content)) {
            $content = Encoder::xmlEncode($content, $options, $error);
            if ($error != null) {
                throw new PayloadException($error->getMessage(), null, $error->getCode());
            }
        }

        return $content;
    }
}
