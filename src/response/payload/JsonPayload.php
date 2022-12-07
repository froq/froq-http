<?php declare(strict_types=1);
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 · http://github.com/froq/froq-http
 */
namespace froq\http\response\payload;

use froq\http\{Response, message\ContentType};
use froq\encoding\encoder\JsonEncoder;

/**
 * A payload class for sending JSON texts as response content with attributes.
 *
 * @package froq\http\response\payload
 * @class   froq\http\response\payload\JsonPayload
 * @author  Kerem Güneş
 * @since   1.0
 */
class JsonPayload extends Payload implements PayloadInterface
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
     * @inheritDoc froq\http\response\payload\PayloadInterface
     */
    public function handle()
    {
        $content = $this->getContent();

        if (is_null($content)) {
            return $content;
        }

        if (!JsonEncoder::isEncoded($content)) {
            // When given in config as "response.json" field.
            $options = (array) $this->response?->app->config('response.json');

            $encoder = new JsonEncoder($options);
            $encoder->setInput($content);

            if ($encoder->encode()) {
                $content = $encoder->getOutput();
            } elseif ($error = $encoder->error()) {
                throw new PayloadException(
                    $error->message, code: $error->code, cause: $error
                );
            }
        }

        return $content;
    }
}
