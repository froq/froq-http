<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};
use froq\http\message\ContentType;
use froq\http\Response;
use froq\encoding\Encoder;

/**
 * Json Payload.
 *
 * @package froq\http\response\payload
 * @object  froq\http\response\payload\JsonPayload
 * @author  Kerem Güneş <k-gun@mail.com>
 * @since   1.0
 */
final class JsonPayload extends Payload implements PayloadInterface
{
    /**
     * Constructor.
     * @param int                     $code
     * @param any                     $content
     * @param array|null              $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, $content, array $attributes = null, Response $response = null)
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

        if (!Encoder::isEncoded('json', $content)) {
            $options = null;
            if ($this->response != null) {
                $options = $this->response->getApp()->config('response.json');
            }

            $content = Encoder::jsonEncode($content, $options, $error);
            if ($error != null) {
                throw new PayloadException($error->getMessage(), null, $error->getCode());
            }
        }

        return $content;
    }
}
