<?php
/**
 * Copyright (c) 2015 · Kerem Güneş
 * Apache License 2.0 <https://opensource.org/licenses/apache-2.0>
 */
declare(strict_types=1);

namespace froq\http\response\payload;

use froq\http\response\payload\{Payload, PayloadInterface, PayloadException};
use froq\http\{Response, message\ContentType};
use froq\encoding\Encoder;

/**
 * Xml Payload.
 *
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
     * @param array|string            $content
     * @param array                   $attributes
     * @param froq\http\Response|null $response
     */
    public function __construct(int $code, $content, array $attributes = null, Response $response = null)
    {
        $attributes['type'] ??= ContentType::APPLICATION_XML;

        parent::__construct($code, $content, $attributes, $response);
    }

    /**
     * @inheritDoc froq\http\response\PayloadInterface
     */
    public function handle()
    {
        $content = $this->getContent();

        if (is_null($content) || is_string($content)) {
            return $content;
        }

        if (!Encoder::isEncoded('xml', $content)) {
            if (!is_array($content)) {
                throw new PayloadException('Content must be array for non-encoded XML payloads, `%s` given',
                    get_type($content));
            }

            $options = null;
            if ($this->response != null) {
                $options = $this->response->getApp()->config('response.xml');
            }

            $content = Encoder::xmlEncode($content, $options, $error);
            if ($error != null) {
                throw new PayloadException($error->getMessage(), null, $error->getCode());
            }
        }

        return $content;
    }
}
