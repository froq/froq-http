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

use froq\http\Response as Container;
use froq\http\message\Body;
use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};

/**
 * Plain Payload.
 * @package froq\http\response\Payload
 * @object  froq\http\response\Payload\PlainPayload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0, 4.0
 */
final class PlainPayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     * @param int                     $code
     * @param string                  $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $container
     */
    public function __construct(int $code, string $content, array $attributes = null,
        Container $container = null)
    {
        $attributes['type'] = Body::CONTENT_TYPE_TEXT_PLAIN;

        parent::__construct($code, $content, $attributes, $container);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        $content = $this->getContent();

        if (!is_null($content) && !is_string($content)) {
            throw new PayloadException('Content must be null or string for plain payloads, '.
                '"%s" given', [gettype($content)]);
        }

        return $content;
    }
}
