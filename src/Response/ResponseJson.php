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

use Froq\Encoding\{Encoder, EncoderException};

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
     * @param  int|null    $statusCode
     * @param  any|null    $data
     * @param  string|null $dataCharset
     * @param  array|null  $headers
     * @param  array|null  $cookies
     * @throws Froq\Encoding\EncoderException
     */
    public function __construct(int $statusCode = null, $data = null, string $dataCharset = null,
        array $headers = null, array $cookies = null)
    {
        $encoder = Encoder::init('json');
        $data = $encoder->encode($data);
        if ($encoder->hasError()) {
            throw new EncoderException(sprintf('JSON Error: %s!', $encoder->getError()));
        }

        $dataType = [Body::CONTENT_TYPE_APPLICATION_JSON, $dataCharset];

        parent::__construct($statusCode, $data, $dataType, $headers, $cookies);
    }
}
