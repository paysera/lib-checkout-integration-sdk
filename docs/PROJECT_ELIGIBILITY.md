## Project Eligibility

Tells the merchant plugin whether the current Paysera project is allowed to collect payments from the current store URL. The SDK calls two backend endpoints (`getProjectInfoById`, `getUrlsByProjectId`), applies the eligibility business rules, and returns a single typed response so plugins do not have to duplicate this logic across WordPress, Magento, PrestaShop, OpenCart, and Shopify integrations.

The eligibility decision combines two checks:

- **Payment collection enabled** — the project's `payment_collection_status` is `enabled` (`disabled` counts as not enabled).
- **URL verified** — the merchant's current store URL is registered against the project and marked as `verified` on the backend. The SDK passes the store URL as the `referrer` query parameter, and the backend returns only the project websites whose host and port match it, so a verified website on another domain never counts. The SDK does no URL matching of its own: an empty list means the store URL is not added to the project.

Only when **both** flags are `true` does the SDK return `eligible`. Any other combination returns `ineligible`.

**Test mode.** When `payment_collection_status` is `test_mode`, payment collection and URL verification are not required, so the merchant can create test payments before verification, review and activation are complete. The store URL must still be added to the project's websites, because the backend rejects test orders whose referrer matches no project website.

- **Store URL added** (verified or not): the SDK returns `eligible`. Every flag reports that capability: `paymentCollectionEnabled` and `urlVerified` are both `true` because nothing about the project's paperwork limits what it can take right now, and `testMode` is `true` (elsewhere `false`) so plugins can surface a test-mode indication.
- **Store URL not added**: the SDK returns `ineligible` with `urlVerified: false`, `paymentCollectionEnabled: true`, `testMode: true` and the single reason `store_url_not_added`. Collection reasons are never reported in test mode, because they do not block test payments.

Live payment collection remains impossible server-side regardless. See the [response shapes](#response-shapes) below.

If either backend call fails after one automatic retry, the SDK returns `failed_to_check` instead of throwing — plugins can render a deterministic Retry UX without wrapping the call in try/catch.

An ineligible response also carries **blocker reasons** — typed categories describing what the merchant has to resolve, so a plugin can point each one at the Merchant Area page where it can actually be fixed. See [Blocker reasons](#blocker-reasons) below, and [Post-authorization banner](#post-authorization-banner) for the copy and links to show for each state.

## Basic usage

```php
<?php

use Paysera\CheckoutSdk\Entity\PaymentApiCredentials;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityCheckRequest;
use Paysera\CheckoutSdk\SdkFacade;
use Paysera\CheckoutSdk\SdkFacadeBuilder;

/**
 * @var SdkFacade $sdkFacade
 */
$sdkFacade = (new SdkFacadeBuilder())
    ->setPaymentApiAuthTokenRepository($paymentApiAuthTokenRepository)
    ->setPaymentApiCredentialsRepository($paymentApiCredentialsRepository)
    ->build()
;

$sdkFacade
    ->getAuthorizationFacade()
    ->authorize(new PaymentApiCredentials($clientId, $clientSecret))
;

$request = new ProjectEligibilityCheckRequest('https://shop.paysera.test');

$response = $sdkFacade
    ->getProjectEligibilityFacade()
    ->checkProjectEligibility($request)
;

switch ($response->getStatus()->getValue()) {
    case 'eligible':
        // Show the Paysera payment option in checkout.
        // $response->isTestMode() === true marks a test-mode project (test payments only).
        break;
    case 'ineligible':
        // Surface one actionable row per blocker.
        foreach ($response->getReasons()->getValues() as $reason) {
            // 'store_url_not_added' | 'store_url_not_confirmed' | 'project_not_submitted_for_review' | 'payment_collection_disabled'
        }

        // The raw flags remain available.
        $paymentEnabled = $response->getPaymentCollectionEnabled(); // bool
        $urlVerified = $response->getUrlVerified();                 // bool
        break;
    case 'failed_to_check':
        // Could not determine eligibility — show a Retry control.
        break;
}
```

The current store URL is passed to the SDK as the `storeUrl` argument on `ProjectEligibilityCheckRequest`. The SDK forwards it to the backend's `referrer` query parameter; the project ID is extracted from the JWT, so plugins never pass it explicitly.

## Blocker reasons

`getReasons()` returns a `ProjectEligibilityReasonCollection` of `ProjectEligibilityReason` value objects; `getValues()` flattens it to plain strings. `getProjectStatus()` exposes the `ProjectStatus` the verdict was derived from, or `null` when eligibility could not be determined.

| Reason | Emitted when | What the merchant has to do |
| --- | --- | --- |
| `store_url_not_added` | no project website matches the store URL's host and port (test mode and live) | add the store URL on the project's websites page |
| `store_url_not_confirmed` | a project website matches the store URL but none of the matching ones is verified (live only) | verify the store URL on the project's websites page |
| `project_not_submitted_for_review` | payment collection is off **and** the project is `draft` or `needs_correction` | submit the project for review |
| `payment_collection_disabled` | payment collection is off for a project past submission (`in_review`, `activated`, `frozen`, `blocked`, `suspended`, `deleted`) | wait for / follow up on the Paysera-side review — the merchant cannot switch collection on themselves, except for `frozen`, which they resume from the merchant area |

Two rules follow from the table:

- A response can carry **at most two** reasons — one URL reason plus one collection reason. The URL reason is either `store_url_not_added` or `store_url_not_confirmed`, never both: a website that is not added cannot be verified.
- The two collection reasons are **mutually exclusive**. `payment_collection_disabled` is the consequence of `project_not_submitted_for_review`, not a second independent blocker, so reporting both would send the merchant to two places for one problem.

The collection is empty for `eligible` and `failed_to_check`, and for test-mode projects whose store URL is added; a test-mode project whose store URL is not added reports `store_url_not_added` only — collection reasons never block test payments. A plugin can therefore drive its whole banner off `getReasons()` without checking the status first.

`ProjectEligibilityReason::REASONS` lists every value, for plugins that map reasons to copy or deep links via a lookup table.

## Post-authorization banner

The SDK ships shared copy for the banner a plugin shows after the merchant has connected, so every integration gives the same next step for the same project state. The English source of every key below is `translations/messages.en.json`. At runtime the keys come from the translation proxy through the Translations facade, which may serve them from cache — after new keys ship, call `Translations::invalidateDataCache()` to pick them up. See [TRANSLATIONS.md](TRANSLATIONS.md). The buttons link to Merchant Area deeplinks — see [MERCHANT_AREA.md](MERCHANT_AREA.md).

### Picking the banner rows

The banner is driven entirely by the existing eligibility response; nothing new is computed and no extra backend call is made.

- `getStatus()` is `eligible` (test mode included): show no banner.
- `getStatus()` is `failed_to_check`: show the plugin's own Retry state (see [Retry and failure contract](#retry-and-failure-contract)). The SDK ships no banner keys for it.
- `getStatus()` is `ineligible`: show **one row per reason** in `getReasons()`, as in the [Blocker reasons](#blocker-reasons) table.
  - `store_url_not_added` always maps to the `store_url_not_added` row (in test mode too).
  - `store_url_not_confirmed` always maps to the `store_url_not_verified` row.
  - `project_not_submitted_for_review` and `payment_collection_disabled` map to a row chosen by `getProjectStatus()`.

Because a response carries at most one URL reason and one collection reason, an ineligible project shows one or two rows: a store URL row (`store_url_not_added` or `store_url_not_verified`), a collection row, or both.

### State map

| State | Trigger | Title key | Description key | CTA key | CTA link |
| --- | --- | --- | --- | --- | --- |
| draft | `project_not_submitted_for_review` + `ProjectStatus::DRAFT` | `eligibility_banner_draft_title` | `eligibility_banner_draft_description` | `eligibility_action_submit_for_review` | `MerchantArea::getRootUrl()` |
| needs_correction | `project_not_submitted_for_review` + `ProjectStatus::NEEDS_CORRECTION` | `eligibility_banner_needs_correction_title` | `eligibility_banner_needs_correction_description` | `eligibility_action_view_corrections` | `MerchantArea::getRootUrl()` |
| in_review | `payment_collection_disabled` + `ProjectStatus::IN_REVIEW` | `eligibility_banner_in_review_title` | `eligibility_banner_in_review_description` | none | none |
| collection_disabled | `payment_collection_disabled` + `ProjectStatus::ACTIVATED` | `eligibility_banner_collection_disabled_title` | `eligibility_banner_collection_disabled_description` | `eligibility_action_complete_verification` | `MerchantArea::getRootUrl()` |
| restricted | `payment_collection_disabled` + `ProjectStatus::BLOCKED` or `ProjectStatus::SUSPENDED` | `eligibility_banner_restricted_title` | `eligibility_banner_restricted_description` | `eligibility_action_contact_support` | none — render the [support block](#support-block) |
| frozen | `payment_collection_disabled` + `ProjectStatus::FROZEN` | `eligibility_banner_frozen_title` | `eligibility_banner_frozen_description` | `eligibility_action_contact_support` | none — render the [support block](#support-block) |
| deleted | `payment_collection_disabled` + `ProjectStatus::DELETED` | `eligibility_banner_deleted_title` | `eligibility_banner_deleted_description` | `eligibility_action_contact_support` | none — render the [support block](#support-block) |
| store_url_not_added | `store_url_not_added` | `eligibility_banner_store_url_not_added_title` | `eligibility_banner_store_url_not_added_description` | `eligibility_action_add_website_address` | `MerchantArea::getWebsitesValidationUrl()` |
| store_url_not_verified | `store_url_not_confirmed` | `eligibility_banner_store_url_not_verified_title` | `eligibility_banner_store_url_not_verified_description` | `eligibility_action_verify_website_address` | `MerchantArea::getWebsitesValidationUrl()` |
| failed_to_check | `getStatus()` is `failed_to_check` | plugin-owned | plugin-owned | plugin-owned Retry | none |
| eligible (test mode included) | `getStatus()` is `eligible` | no banner | — | — | — |

Notes on the links:

- **draft** and **needs_correction** land on the project overview: review submission and the correction message both render there, and the Merchant Area has no dedicated route for either.
- **collection_disabled** also lands on the project overview. The Merchant Area has no dedicated identity verification or service agreement page, so the overview is the closest landing page.
- **store_url_not_added** and **store_url_not_verified** both land on the project's websites page, where the merchant both adds and verifies store URLs.
- **restricted** never shows why the project is blocked or suspended. A block may be risk or compliance driven, so Paysera support explains it, not the plugin; the SDK does not expose a reason either.

The keys `eligibility_banner_reason_store_url_not_added`, `eligibility_banner_reason_store_url_not_confirmed`, `eligibility_banner_reason_not_submitted_for_review`, `eligibility_banner_reason_payment_collection_disabled` and `eligibility_action_fix_it` form the generic per-reason banner. Apart from `eligibility_banner_reason_store_url_not_added`, they predate the state map and stay available for plugins that have not adopted it yet. `eligibility_banner_reason_store_url_not_added` ships together with the `store_url_not_added` row and is that row's fallback.

The generic per-reason keys are also the fallback for the state map:

- **Unknown project status.** `ProjectStatus` accepts any string, and the evaluator reports `payment_collection_disabled` for every status other than `draft` and `needs_correction`. A status that has no row in the table — for example one added on the Paysera side later — renders the generic row for its reason (`eligibility_banner_reason_payment_collection_disabled`) with `eligibility_action_fix_it` linking to `MerchantArea::getRootUrl()`.
- **Missing translation.** `Translator::translate()` returns `null` when the translation proxy request failed or the key is not in the fetched catalogue. When a state key resolves to `null`, render the generic row for that reason instead.

### Store URL placeholder

`eligibility_banner_store_url_not_added_description` and `eligibility_banner_store_url_not_verified_description` both contain a `%store_url%` placeholder. `Translator::translate()` returns the raw string without substitution, so the plugin replaces `%store_url%` itself with the same store URL it passed to `ProjectEligibilityCheckRequest`:

```php
<?php

use Paysera\CheckoutSdk\Entity\ProjectEligibilityCheckRequest;
use Paysera\CheckoutSdk\Entity\ProjectEligibilityReason;

$storeUrl = 'https://shop.paysera.test';
$locale = 'en';
$translator = $sdkFacade->getTranslationsFacade()->getTranslator();

$response = $sdkFacade
    ->getProjectEligibilityFacade()
    ->checkProjectEligibility(new ProjectEligibilityCheckRequest($storeUrl))
;

// Each store URL reason maps to its state description key and its generic per-reason fallback key.
$descriptionKeysByReason = [
    ProjectEligibilityReason::STORE_URL_NOT_ADDED => [
        'eligibility_banner_store_url_not_added_description',
        'eligibility_banner_reason_store_url_not_added',
    ],
    ProjectEligibilityReason::STORE_URL_NOT_CONFIRMED => [
        'eligibility_banner_store_url_not_verified_description',
        'eligibility_banner_reason_store_url_not_confirmed',
    ],
];

$description = null;

foreach ($response->getReasons()->getValues() as $reason) {
    if (!isset($descriptionKeysByReason[$reason])) {
        continue;
    }

    [$stateKey, $fallbackKey] = $descriptionKeysByReason[$reason];

    // null when the proxy failed or the key is missing: fall back to the generic per-reason key
    $template = $translator->translate($stateKey, $locale)
        ?? $translator->translate($fallbackKey, $locale);

    if ($template !== null) {
        $description = str_replace('%store_url%', $storeUrl, $template);
    }
}
```

A response carries at most one store URL reason, so the loop sets `$description` at most once.

### Support block

Every contact-support state (restricted, frozen, deleted) renders the same support block, built from these existing keys:

| Key | Use |
| --- | --- |
| `support_header` | heading of the block |
| `support_email_label` | label for the email line |
| `support_email` | support email address |
| `support_phone_label` | label for the phone line |
| `support_phone` | support phone number |
| `support_hours_label` | label for the working hours line |
| `support_hours` | working hours |

Define the block once and reuse it for each of these states.

### Deeplink availability

`getRootUrl()`, `getCredentialsUrl()` and `getWebsitesValidationUrl()` return `null` when no authorization token is stored or it cannot be decoded (see [MERCHANT_AREA.md](MERCHANT_AREA.md)). When the CTA link is `null`, render the banner without the button. `getProjectListUrl()` needs no project id and never returns `null`.

### Before authorization

No eligibility check runs before the merchant connects, and no project id is known yet. To help the merchant find their API credentials, show `credentials_location_merchant_area` as the hint and `credentials_location_action` as the button, linked to `MerchantArea::getProjectListUrl()` — the Merchant Area project list, from which the merchant opens their project. After authorization, `MerchantArea::getCredentialsUrl()` links straight to the project's API credentials.

The shorter `credentials_location_text` hint stays available for plugins that keep their current credentials form wording.

## Response shapes

`checkProjectEligibility()` always returns a `ProjectEligibilityResponse` with six fields: `status`, `paymentCollectionEnabled`, `urlVerified`, `testMode`, `reasons`, `projectStatus`. The combinations below cover every possible outcome.

**Eligible — both checks pass:**

```php
[
    'status' => 'eligible',
    'payment_collection_enabled' => true,
    'url_verified' => true,
    'test_mode' => false,
    'reasons' => [],
    'project_status' => 'activated',
]
```

**Eligible — test mode, store URL added:**

```php
[
    'status' => 'eligible',
    'payment_collection_enabled' => true,
    'url_verified' => true, // verification is not required in test mode, so it is not reported as missing
    'test_mode' => true,
    'reasons' => [],
    'project_status' => 'draft',
]
```

**Ineligible — test mode, store URL not added:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => true,
    'url_verified' => false,
    'test_mode' => true,
    'reasons' => ['store_url_not_added'],
    'project_status' => 'draft',
]
```

In test mode a plugin therefore shows no "verify your URL" or "payments disabled" prompt — those describe what blocks live payments, and test payments need neither. The one prompt it can show is the `store_url_not_added` row, because test orders from a store URL that is not added are rejected. The verification and collection prompts return as soon as test mode is switched off and the next check reports the project's real state.

**Ineligible — payment collection disabled:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => false,
    'url_verified' => true,
    'test_mode' => false,
    'reasons' => ['payment_collection_disabled'],
    'project_status' => 'in_review',
]
```

**Ineligible — store URL not added:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => true,
    'url_verified' => false,
    'test_mode' => false,
    'reasons' => ['store_url_not_added'],
    'project_status' => 'activated',
]
```

**Ineligible — store URL added but not verified:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => true,
    'url_verified' => false,
    'test_mode' => false,
    'reasons' => ['store_url_not_confirmed'],
    'project_status' => 'activated',
]
```

**Ineligible — both checks fail:**

```php
[
    'status' => 'ineligible',
    'payment_collection_enabled' => false,
    'url_verified' => false,
    'test_mode' => false,
    'reasons' => ['store_url_not_confirmed', 'project_not_submitted_for_review'],
    'project_status' => 'draft',
]
```

**Failed to check — eligibility could not be determined:**

```php
[
    'status' => 'failed_to_check',
    'payment_collection_enabled' => null,
    'url_verified' => null,
    'test_mode' => null,
    'reasons' => [],
    'project_status' => null,
]
```

All three flags are `null` (rather than `false`) on `failed_to_check` because the SDK cannot distinguish a disabled-on-purpose (or non-test-mode) project from one whose status it never observed. Plugins should treat `null` as "unknown" and prompt the merchant to retry.

## Retry and failure contract

For each backend call, the SDK performs one automatic retry after a 1-second delay on transient failures (HTTP `5xx`, `408 Request Timeout`, `429 Too Many Requests`, and transport-level errors). Token-expiry (`401`) is handled separately by the existing token-refresh loop. Authoritative client errors (`400`, `403`, `404`, `422`, etc.) are not retried — they are deterministic and would not change on a second attempt.

If a call still fails after the retry, the SDK swallows the exception, logs it with full context, and returns the `failed_to_check` response shown above. The previous failure state is not cached: each call to `checkProjectEligibility()` issues fresh HTTP requests, so a second invocation after `failed_to_check` (e.g. from a Retry button) will re-attempt and produce the actual current eligibility.

`checkProjectEligibility()` itself never throws. The closest wrapper that does throw is `PaymentApiClient` directly, which is not part of the public eligibility API.
