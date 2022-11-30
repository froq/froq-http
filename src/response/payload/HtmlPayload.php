<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\{Response, message\ContentType};

/**
 * A payload class for sending HTML texts as response content with attributes.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\HtmlPayload
 * @author  Kerem Güneş
 * @since   1.0
 */
class HtmlPayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     *
     * @param int                     $code
     * @param string|null             $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, string|null $content, array $attributes = null, Response $response = null)
    {
        $attributes['type'] = ContentType::TEXT_HTML;

        parent::__construct($code, $content, $attributes, $response);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        $content = $this->getContent();

        if (!is_null($content) && !is_string($content)) {
            throw new PayloadException('Content must be string|null for html payloads, %s given',
                get_type($content));
        }

        return $content;
    }
}
