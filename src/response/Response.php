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

namespace froq\http\response;

use froq\http\HttpException;

/**
 * Response.
 * @package froq\http\response
 * @object  froq\http\response\Response
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
class Response
{
    /**
     * Status code.
     * @var int
     */
    protected $statusCode;

    /**
     * Content.
     * @var any
     */
    protected $content;

    /**
     * Content type.
     * @var string
     */
    protected $contentType;

    /**
     * Content charset.
     * @var string
     */
    protected $contentCharset;

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
     * @param  int        $statusCode
     * @param  array|null $contentStack
     * @param  array|null $headers
     * @param  array|null $cookies
     */
    public function __construct(int $statusCode, array $contentStack = null,
        array $headers = null, array $cookies = null)
    {
        $this->statusCode = $statusCode;

        @ [$content, $contentType, $contentCharset] = (array) $contentStack;
        $this->content = $content;
        $this->contentType = $contentType ?? Body::CONTENT_TYPE_HTML;
        $this->contentCharset = $contentCharset ?? Body::CONTENT_CHARSET_UTF_8;

        $this->headers = (array) $headers;
        $this->cookies = (array) $cookies;
    }

    /**
     * Get status code.
     * @return int
     */
    public final function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get content.
     * @return any|null
     */
    public final function getContent()
    {
        return $this->content;
    }

    /**
     * Get content type.
     * @return string
     */
    public final function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Get content charset.
     * @return string
     */
    public final function getContentCharset(): string
    {
        return $this->contentCharset;
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

    /**
     * Prepare content array.
     * @param  any    $contentStack
     * @param  string $contentType
     * @return array
     * @throws froq\http\HttpException
     */
    protected final function prepareContentStack($contentStack, string $contentType): array
    {
        // for proper extract
        if (is_assoc_array($contentStack)) {
            $contentStack = [$contentStack];
        }

        if (is_array($contentStack)) {
            $content = $contentStack[0] ?? null;
            $contentCharset = $contentStack[1] ?? null;
        } else {
            $content = $contentStack;
            $contentCharset = null;
        }

        $contentTypeCheck = gettype($content);
        switch ($contentTypeCheck) {
            case 'NULL':
                return [$content, $contentType, $contentCharset];
            // array/object types
            case 'array': case 'object':
                // encodable?
                if (!strpos($contentType, '/json') && !strpos($contentType, '/xml')) {
                    throw new HttpException("Array/object contents encodable for only JSON and XML".
                        " responses ('{$contentTypeCheck}' given)");
                }
                return [$content, $contentType, $contentCharset];
            // scalar types
            case 'string': case 'integer': case 'double': case 'boolean':
                return [(string) $content, $contentType, $contentCharset];
        }

        throw new HttpException("Unsupported content stack type '{$contentTypeCheck}'");
    }
}
