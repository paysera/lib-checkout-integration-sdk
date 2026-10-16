<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Factory;

use Http\Client\Curl\Client;
use Psr\Http\Client\ClientInterface;

class HttpClientFactory
{
    public function create(): ClientInterface
    {
        return new Client(
            null,
            null,
            [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 60,
            ]
        );
    }
}
