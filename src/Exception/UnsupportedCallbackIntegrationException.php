<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Exception;

/**
 * @deprecated since 3.0.0. The SDK no longer throws this exception: a callback whose event has no
 *             registered handler is returned as Paysera\CheckoutSdk\Entity\UnsupportedCallback. The
 *             class is kept so existing use and catch statements keep resolving, and will be removed
 *             in a future major version. Replace catch (UnsupportedCallbackIntegrationException $e)
 *             with an instanceof check on the processCallback() result.
 */
class UnsupportedCallbackIntegrationException extends IntegrationException
{
}
