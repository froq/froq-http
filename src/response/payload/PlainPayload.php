<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};
use froq\http\message\Body;
use froq\http\Response;

/**
 * Plain Payload.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\PlainPayload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   3.0
 */
final class PlainPayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     * @param int                     $code
     * @param string                  $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, string $content, array $attributes = null,
        Response $response = null)
    {
        $attributes['type'] = Body::CONTENT_TYPE_TEXT_PLAIN;

        parent::__construct($code, $content, $attributes, $response);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        $content = $this->getContent();

        if (!is_null($content) && !is_string($content)) {
            throw new PayloadException('Content must be string or null for plain payloads, "%s" '.
                'given', [gettype($content)]);
        }

        return $content;
    }
}
