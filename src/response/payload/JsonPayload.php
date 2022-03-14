<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\{Response, message\ContentType};
use froq\encoding\encoder\Encoder;

/**
 * Json Payload.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\JsonPayload
 * @author  Kerem Güneş
 * @since   1.0
 */
final class JsonPayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     *
     * @param int                     $code
     * @param mixed                   $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, mixed $content, array $attributes = null, Response $response = null)
    {
        $attributes['type'] ??= ContentType::APPLICATION_JSON;

        parent::__construct($code, $content, $attributes, $response);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        $content = $this->getContent();

        if (is_null($content)) {
            return $content;
        }

        if (!Encoder::isEncoded('json', $content)) {
            $options = $this->response?->getApp()->config('response.json');

            /** @var froq\encoding\encoder\JsonEncoder */
            $encoder = Encoder::create('json', (array) $options);
            $encoder->setInput($content);

            if (!$encoder->encode($error)) {
                throw new PayloadException($error->message, code: $error->code, cause: $error);
            }

            $content = $encoder->getInput();
            unset($encoder); // Free.
        }

        return $content;
    }
}
