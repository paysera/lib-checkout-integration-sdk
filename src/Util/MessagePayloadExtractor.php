<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Util;

use Psr\Http\Message\MessageInterface;

class MessagePayloadExtractor
{
    public function getPayload(MessageInterface $message): string
    {
        $body = $message->getBody();

        $body->rewind();
        $payload = $body->getContents();
        $body->rewind();

        return $payload;
    }
}
