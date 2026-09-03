# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 3.1.0
### Added
- **Typed eligibility blocker reasons.** `ProjectEligibilityResponse` now reports *why* a project is ineligible, not just that it is. `getReasons(): ProjectEligibilityReasonCollection` returns the blocker categories behind the verdict, and `getProjectStatus(): ?ProjectStatus` exposes the project status the verdict was derived from. Until now plugins received only `getPaymentCollectionEnabled()` and `getUrlVerified()`, which cannot distinguish a project still waiting to be submitted for review from one whose payment collection was switched off after review — so every ineligible state rendered the same generic "go to Merchant Area" link and left the merchant without a next step. The categories are the `ProjectEligibilityReason` constants:
  - `store_url_not_confirmed` — the current store URL is not verified against the project.
  - `project_not_submitted_for_review` — payment collection is off and the project is `draft` or `needs_correction`.
  - `payment_collection_disabled` — payment collection is off for a project that is past submission (`in_review`, `activated`, `blocked`, `suspended`, `deleted`).

  A project can report both a URL reason and one collection reason at the same time; the two collection reasons are mutually exclusive, because "not submitted" is the cause of "collection disabled" rather than a second, separate blocker. `getLoggerData()` gained `reasons` and `project_status` keys. Fully backward compatible: both accessors are new, the existing flags and constructor signature are unchanged, and callers that never pass the new constructor arguments get an empty reason collection and a `null` status. See [docs/PROJECT_ELIGIBILITY.md](docs/PROJECT_ELIGIBILITY.md).

  **What plugins should retest after upgrading:**
  - **Existing eligibility handling** is unchanged — `eligible` / `ineligible` / `failed_to_check` and the two boolean flags resolve exactly as before.
  - **Reason mapping**: a `draft` project with an unverified URL reports both `store_url_not_confirmed` and `project_not_submitted_for_review`; an `activated` project with collection off reports `payment_collection_disabled`.
  - **Eligible and `failed_to_check` responses** carry an empty reason collection, so a banner driven by `getReasons()` renders nothing for them.
  - **Test-mode projects** stay `eligible` with no reasons.

## 3.0.0
### Breaking Changes
- **An unsupported callback event is no longer an exception.** `getCallbacksFacade()->processCallback()` now returns the new `UnsupportedCallback` object for a verified, well-formed callback whose event has no registered handler, and logs it at `info` instead of `warning`. It previously threw `UnsupportedCallbackIntegrationException`. Paysera adds event types independently of SDK releases, so receiving one this SDK version does not handle is a normal, expected outcome — not a failure. Modelling it as an exception forced every integration into a catch block whose only job was to answer `200`, made an ignored event indistinguishable from a real integration error in the logs, and turned a forgotten catch into an HTTP 500 that the sender then retried for hours. Integrations that already guard with `if (!$callback instanceof OrderAmountPaidCallback)` need no change; integrations that relied on the catch must move that branch to an `instanceof` check, because the catch block is now dead code. One break can fail hard: **code that passes the `processCallback()` result into anything typed `OrderAmountPaidCallback`** — a typed parameter, property, or framework argument resolver — now receives an `UnsupportedCallback` and raises a `TypeError` where it previously caught an exception; those call sites must guard with `instanceof` before handing the value on. See [docs/PROCESS_PAYMENT_CALLBACK.md](docs/PROCESS_PAYMENT_CALLBACK.md).

  **What plugins should retest after upgrading:**
  - **A supported `order:amount_paid_updated` callback** still returns `OrderAmountPaidCallback` and updates the order exactly as before.
  - **A callback carrying an event the SDK does not handle** now returns `UnsupportedCallback`, the endpoint answers `200`, no order is touched, and the log line is `info` rather than `warning`.
  - **Every path that types the result as `OrderAmountPaidCallback`** is guarded by `instanceof` before the value is passed on.
  - **Log and alerting rules keyed on `warning` plus "Unsupported callback received"** are re-pointed at `info`, or removed.

- **A malformed callback payload is rejected instead of degrading to "unsupported".** `CallbackEventBuilder::build()` now throws when the payload carries no `event` object, when `event` is not an object, or when `event.name` / `event.type` is missing, is not a string or number, or is empty once trimmed; `processCallback()` surfaces it as `CallbackBuildIntegrationException` (suggested `400`). Previously the builder coerced any of those to an empty event, which matched no handler and was reported back to Paysera as an accepted-and-ignored `200` — a broken or truncated delivery looked exactly like a future event type and was silently dropped. Note the operational consequence: a persistently malformed payload will now be retried by the sender until it gives up, which is the signal that something is actually wrong. The event-build step also moved inside the facade's error handling; it previously sat outside any try/catch, so a builder failure would have escaped as a raw internal exception rather than the documented `CallbackBuildIntegrationException`.

  **What plugins should retest after upgrading:**
  - **A payload with no `event` key, with `event` as a string, or with an empty or whitespace-only `event.type`** returns `400`, and the log carries the per-field validation context under an `exception_context` key alongside the exception.
  - **A well-formed but unknown event** still returns `200` with `UnsupportedCallback` — this is the case that must not become `400`.

### Added
- **`Paysera\CheckoutSdk\Entity\UnsupportedCallback`.** Implements `CallbackInterface`; `getEvent()` returns the parsed event as a `CallbackEventInterface`, so an integration can log or route on `getType()` / `getName()` without the SDK supporting that event. `getPayload(): array` hands back the decoded body, so an integration that already knows what to do with an event can act on it or store it instead of discarding it — the handler list is fixed inside the SDK and support for a new event type ships in a future release. That payload has passed signature verification but has not been normalized or validated by the SDK.
- **`CallbackEventValidator`.** Field validation for the callback event moved out of `CallbackEventBuilder` onto a validator built from the existing `StringValidator` / `ErrorBag` infrastructure, so the callback pipeline follows the same build-then-validate shape as the rest of the SDK. A class of the same name was removed in 1.1.0; that one checked events against an allow-list of supported types, a role the callback handler registry has held since. This one only checks that `event.type` and `event.name` are present.

### Deprecated
- **`UnsupportedCallbackIntegrationException`** — never thrown by the SDK anymore. The class is kept so existing `use` and `catch` statements keep resolving, and will be removed in a future major version. Replace `catch (UnsupportedCallbackIntegrationException $exception)` with an `instanceof` check on the `processCallback()` result.

### Documentation
- **`docs/PROCESS_PAYMENT_CALLBACK.md` rewritten for the new contract.** The supported-events table now lists `UnsupportedCallback` as the fallback return type, the usage snippet branches on `instanceof` instead of catching, the exception table is down to the two exceptions that remain, and a "Migrating from 2.x" section shows the before/after.

## 2.4.0
### Added
- **Typed buyer consent accessor.** `getTranslationsFacade()->getCustomerConsent(?string $locale, ?string $pluginNamespace): CustomerConsent` returns the three strings of the pre-payment consent notice together — the sentence (`getText()`, carrying the single `%s` placeholder where the link belongs), the rules URL (`getLinkUrl()`) and the anchor text (`getLinkText()`) — already localized. Until now every plugin had to know the raw keys `payment_methods_customer_consent`, `payment_methods_customer_consent_link` and `payment_methods_customer_consent_link_content`, call `getTranslator()` itself, and invent its own fallback for each; several instead hardcoded a per-language map of rules URLs that then went stale. Each field falls back to its English source value when the translations carry no value for it, and the whole notice falls back when the translations request fails — on the same terms as `getTranslator()`, which it delegates to. The defaults and keys are public constants on `CustomerConsentProvider` (`DEFAULT_TEXT`, `DEFAULT_LINK_URL`, `DEFAULT_LINK_TEXT`, `TEXT_KEY`, `LINK_URL_KEY`, `LINK_TEXT_KEY`) so a plugin can seed an offline translation bundle without copying the strings. See [docs/TRANSLATIONS.md](docs/TRANSLATIONS.md).

  **What plugins should retest after upgrading:**
  - **Consent notice renders** with the localized rules URL for a store locale that has a localized rules page (`lt`, `lv`, `et`, `pl`, `ru`, `bg`, `ro`, `de`), and with the English page for one that does not (`fr`, `es`, `uk`, `ka`, `sq`).
  - **Translations proxy unreachable with a cold cache** still renders a complete notice pointing at the English rules page, instead of an empty `href`.
  - **Existing `getTranslator()` callers** are unaffected — the facade gained a constructor argument, but it is resolved by the SDK container, so only code that constructs `Translations` by hand needs updating.

### Fixed
- **Stale rules link in the bundled English translations.** `translations/messages.en.json` still carried `payment_methods_customer_consent_link` pointing at the superseded `https://www.paysera.com/v2/en-GB/legal/pis-rules-2020`. It now matches what the translation proxy already serves, `https://www.paysera.com/v2/en/legal/rules-for-the-provision-of-the-payment-initiation-service`. This file is `export-ignore`d and never reaches a Composer install, so this has no runtime effect — it is the seed the translation namespace is populated from.

### Documentation
- **`docs/TRANSLATIONS.md` error handling corrected.** It claimed `getTranslator()` throws `IntegrationException` when the proxy request fails. It does not, and never has on this code path: the facade catches `BaseException`, logs it, and returns an empty `Translator` whose `translate()` yields `null` for every key. The section now describes the actual contract and shows the caller-side default that it implies.

## 2.3.0
### Added
- **Test-mode projects are eligible.** Project eligibility now recognizes the `payment_collection_status = test_mode` state: a test-mode project is reported `eligible` unconditionally — neither payment collection nor URL verification is required — so a merchant can connect and create test payments before URL verification, review and activation are complete. The `ProjectEligibilityResponse` gains a fourth flag exposed via `isTestMode(): ?bool` (`true` on the test-mode path, `false` when observed and not in test mode, `null` on `failed_to_check`, mirroring the existing flags), and `getLoggerData()` now emits a `test_mode` key. `ProjectInfo::isTestMode(): bool` exposes the same state at the project level. Live payment collection remains impossible server-side regardless. Fully backward compatible: non-test-mode eligibility is unchanged. See [docs/PROJECT_ELIGIBILITY.md](docs/PROJECT_ELIGIBILITY.md).

  **What plugins should retest after upgrading:**
  - **Non-test-mode eligibility** still resolves identically (`enabled` + verified URL → eligible; every other combination → ineligible).
  - **Test-mode project** (`payment_collection_status = test_mode`) now resolves to `eligible` with `isTestMode() === true`, even for a draft, unverified, unreviewed project.
  - **`failed_to_check`** still returns all flags as `null`, now including `test_mode`.

## 2.2.0
### Added
- **Merchant Area website-verification deeplink.** `getMerchantAreaFacade()->getWebsitesValidationUrl(): ?string` returns the project-specific Merchant Area page where the merchant adds and verifies the project's store URLs (`[base]/shell/checkout/{project_id}/settings/websites`). It follows the same contract as the existing Merchant Area deeplinks: the project id is read from the stored authorization token, the base host is selected from the configured environment (overridable via `PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_*_BASE_URL`), and it returns `null` when no token is stored, it cannot be decoded, or the decoded project id is empty. See [docs/MERCHANT_AREA.md](docs/MERCHANT_AREA.md).
- **Typed order-request construction with a fixed `source` allow-list.** New `getPaymentsFacade()->buildPaymentOrderCreateRequestFromValues(Purchase, Metadata, ?RedirectUrls, ?string $source)` builds a `PaymentOrderCreateRequest` from typed value objects instead of a raw array. `source` is now a fixed set defined by the new `PaymentOrderSource` constants holder (`PaymentOrderSource::SOURCES` — `checkout_page`, `product_page_express_checkout`); the typed path rejects any value outside it with a `ValidationException`, and its typed `Metadata` argument prevents misspelled metadata keys from silently leaking to the wire. This stays fully backward compatible: the raw-array `buildPaymentOrderCreateRequest(array)` path is unchanged — callers passing no source, or a free-form source string, are unaffected, and unrecognized `metadata.*` keys are still forwarded as custom metadata exactly as before. See [docs/INITIATE_PAYMENT.md](docs/INITIATE_PAYMENT.md).

  **What plugins should retest after upgrading:**
  - **Existing raw-array order creation** still builds and sends identically, with and without `source`.
  - **New typed path**: a valid `PaymentOrderSource` value is accepted end-to-end; an invalid string throws a `ValidationException` before send.
  - **Metadata correctness**: on the raw-array path, confirm `referer` is spelled correctly — unknown keys (e.g. `referrer`) are forwarded as custom metadata by design.

### Deprecated
- **`Payments::buildPaymentOrderCreateRequest(array $orderData)`** — kept for backward compatibility and delegating unchanged, but deprecated in favour of the typed `buildPaymentOrderCreateRequestFromValues()`; it will be removed in a future major version. The raw-array path does not validate the `source` allow-list and silently forwards misspelled `metadata.*` keys as custom metadata.

## 2.1.0
### Added
- **Optional `cancel_url` on order creation.** The payment-order create request now accepts an optional `redirect_urls.cancel_url` — the buyer's "back to shop" return URL — exposed via `RedirectUrls::getCancelUrl(): ?string` and the fourth constructor argument of `RedirectUrls`. It is validated as an HTTPS URL (max 2048 characters) only when provided, and stays fully backward compatible: existing integrations that omit it are unaffected. See [docs/INITIATE_PAYMENT.md](docs/INITIATE_PAYMENT.md).

## 2.0.0
### Breaking Changes
- **Payment-method logos now come exclusively from the API.** The SDK no longer ships bundled SVG icons or resolves them locally. Removed `PaymentMethodIconResolver`, the entire `assets/payment-methods/` icon set, and `SdkFacadeBuilder::setPaymentMethodIconBaseUrl()`.
- **`PaymentMethod` now reads the `logo_url` field** from the API response (previously `icon_url`). When the API does not provide a logo URL, `getLogoUrl()` (and the deprecated `getIconUrl()`) return an empty string — integrations must handle the empty case (e.g. render their own placeholder) instead of relying on a bundled fallback.

### Added
- **`PaymentMethod::getLogoUrl(): string`** — the payment-method logo URL provided by the API.

### Deprecated
- **`PaymentMethod::getIconUrl(): string`** — delegates to `getLogoUrl()`; kept for backward compatibility and will be removed in a future major version.

## 1.4.0
### Added
- **Merchant Area deeplinks.** New `$sdkFacade->getMerchantAreaFacade()` exposes `getRootUrl(): ?string` (`[base]/shell/checkout/{project_id}/overview`) and `getCredentialsUrl(): ?string` (`[base]/shell/checkout/{project_id}/integrations`). The project id is read from the stored authorization token and the base host is selected from the configured environment, so integrations consume a ready URL instead of building it themselves. Both methods return `null` when no token is stored, it cannot be decoded, or the decoded project id is empty. Base hosts are overridable via `PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_PRODUCTION_BASE_URL` / `PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_SANDBOX_BASE_URL`. See [docs/MERCHANT_AREA.md](docs/MERCHANT_AREA.md).

  **What plugins should retest after upgrading:**
  - **"Go to Merchant Area" / settings deeplinks.** If the plugin previously built the Merchant Area URL itself (e.g. with a regex over `client_id` and a hard-coded host), switch it to `getMerchantAreaFacade()->getRootUrl()` / `getCredentialsUrl()` and verify the merchant lands on the correct project — both in **production** and **sandbox** mode.
  - **Logged-out / not-yet-connected state.** Open the plugin's settings page when no authorization token is stored, the token is invalid, or the project id is empty: both methods must return `null` and the page must render without errors (no broken link, no exception). The deeplink must never break the host page.
  - **Sandbox base host.** The sandbox host (`https://sandbox.bank.paysera.com`) is currently an **assumption** — confirm the merchant actually reaches the sandbox Merchant Area, or override it via `PAYSERA_CHECKOUT_SDK_MERCHANT_AREA_SANDBOX_BASE_URL` until the real host is confirmed.
  - **Special characters in the project id.** The `project_id` is `rawurlencode`d into the path, so retest with a project whose id is a non-ASCII / non-UUID value if applicable.

### Changed
- **Internal: shared URL-building base class.** `PaymentApiUrlProvider` was refactored to extend the new `AbstractApiUrlProvider` (the constructor, `buildUrl()`, and `getBaseUrl()` moved into the base class) so it can be shared with `MerchantAreaUrlProvider`. **No behaviour change is expected** for existing Payment API URLs — placeholders are still substituted without URL-encoding and the `PAYSERA_*_BASE_URL` env-override warnings are unchanged. **Retest the existing payment/refund/eligibility flows that build API URLs (payment order & link creation, callbacks, project info/eligibility) in both production and sandbox** to confirm the refactor is transparent.

## 1.3.0
### Added
- **LKU Credit Union and UBB payment method icons.** The SDK now ships `lku.svg` and `ubb.svg` under `assets/payment-methods/`, and `PaymentMethodIconResolver` resolves icon URLs for the `lku` and `ubb` payment method keys. See [docs/PAYMENT_METHOD_ICONS.md](docs/PAYMENT_METHOD_ICONS.md).

### Changed
- **Refreshed Swedbank and card-payment icons.** Both SVGs are replaced with the current design assets; `swedbank.svg` is additionally minified (158 KiB → 86 KiB) with no visual change. `card-payment.svg` intrinsic dimensions changed from 83×18 to 48×48 to match the rest of the icon set — integrations relying on the SVG's natural size may need a layout check.
- **Documented the `wise` icon key.** `docs/PAYMENT_METHOD_ICONS.md` now lists `wise` among the supported payment method keys (the icon itself shipped in an earlier release).

## 1.2.0
### Added
- **Configurable JWT clock-skew leeway.** `SdkFacadeBuilder::setJwtLeeway(int $seconds)` sets the allowance applied when validating JWT time claims (`iat`, `nbf`, `exp`). The SDK default stays `0` (strict — the SDK does not relax validation on its own); the consuming integration opts into a tolerance. A non-zero leeway absorbs clock drift between the integrating server and Paysera, which otherwise fails authorization with `BeforeValidException` ("Cannot handle token with iat prior to ..."). RFC 7519 allows a small leeway for clock skew.
- **Structured diagnostics on JWT validation failures.** `JwtDecoder` now logs a non-sensitive structured context for each failure class (`reason`, `exception_class`, `local_time`, `configured_leeway_seconds`, and the token's `iat`/`exp`/`nbf` with their exact offset from local time, e.g. `iat_minus_local_seconds`). Clock-skew (`BeforeValidException`), expiry, invalid-signature, and invalid-claim failures are distinguished, so the root cause is identifiable from a single log line. The raw token and signature are never logged.

## 1.1.0
### Breaking Changes
- **The Callbacks facade now exposes a single entry point.** Removed `processPaymentCallback()` and `processRefundCallback()`; use `processCallback(RequestInterface $request): CallbackInterface` instead. The SDK resolves the event from the payload and returns the matching typed callback object. Events the SDK does not support raise `UnsupportedCallbackIntegrationException`, which consumers should acknowledge with HTTP 200 so the callback is not retried.
- **Renamed callback entities.** `PaymentCallback` is now `OrderAmountPaidCallback`. The event class `Entity\PaymentCallback\Event` moved up one namespace level and is now `Entity\CallbackEvent`. The `Entity\PaymentCallback\` namespace (`Order`, `Payment`, `PaymentLink`, …) is now `Entity\OrderAmountPaidCallback\`.
- **Renamed callback services.** `PaymentCallbackNormalizer` is now `OrderAmountPaidCallbackNormalizer`; `PaymentCallbackValidator` is now `OrderAmountPaidCallbackValidator`.
- **Removed unused callback APIs.** Deleted `RefundCallback`, `RefundCallbackNormalizer`, `RefundCallbackValidator`, `PaymentApiCallbackBuilder`, and `CallbackEventValidator`.

### Added
- **`CallbackInterface`** — a common return type for callback objects exposing `getEvent(): CallbackEventInterface`, implemented by `OrderAmountPaidCallback`. Consumers distinguish callbacks with `instanceof` (for example `$callback instanceof OrderAmountPaidCallback`).
- **Registry-based callback dispatch** — `CallbackEventBuilder`, `CallbackHandlerRegistry`, `CallbackHandlerInterface`, and `OrderAmountPaidCallbackHandler`. Support for a new callback type is added by registering a handler, without changing the facade.

### Changed
- **Callback support is now checked before denormalization.** The pipeline is verify → decode → build event → resolve handler → handle, so a payload of an unknown or differently-shaped event is rejected by its event type instead of failing deep inside order-structure validation. Verification, build, and unsupported failures now map to distinct exceptions: `CallbackVerificationIntegrationException`, `CallbackBuildIntegrationException`, and `UnsupportedCallbackIntegrationException`.
- Rewrote [docs/PROCESS_PAYMENT_CALLBACK.md](docs/PROCESS_PAYMENT_CALLBACK.md) for the new callback API.

## 1.0.2
### Dependencies
- **`psr/container` constraint widened from `^2.0` to `^1.1 || ^2.0`.** The SDK only consumes `Psr\Container\ContainerInterface`, whose signatures (`get(string $id)`, `has(string $id): bool`) are identical across the 1.1 and 2.0 lines, so the SDK's `Container::get(string $id): object` implementation is a valid covariant fit for both. The previous `^2.0` floor was unnecessarily strict and made the SDK uninstallable alongside frameworks that pin `psr/container ^1` through their service-container stack (for example, applications built on the laminas component suite), since a single PHP process can only load one `ContainerInterface`. Widening the range resolves that conflict with no code or behavioral change; the package still resolves to `psr/container` 2.0.x on PHP 7.4+ in its own environment.

## 1.0.1
### Changed
- **`.gitattributes` tightened so the Composer dist tarball only contains what consumers actually need.** Added `.gitlab-ci.yml`, `CLAUDE.md`, `CODEOWNERS`, `Dockerfile`, `Makefile`, and `translations/` to `export-ignore` — these are either internal repository tooling or unused data files that previously shipped to `vendor/` for no benefit. `CHANGELOG.md` and `docs/` are now included in the dist so installed-package users can read release notes and usage guides without leaving their project tree.

## 1.0.0
### Added
- First public release on [github.com/paysera/lib-checkout-integration-sdk](https://github.com/paysera/lib-checkout-integration-sdk) and Packagist.
- `LICENSE` file with the full LGPL-3.0-or-later text.
- `CONTRIBUTING.md` describing the GitLab-as-source-of-truth workflow, contribution path, and local quality-gate commands.
- `SECURITY.md` with the vulnerability-disclosure policy, supported-versions table, and reporter SLA.
- `composer.json` now exposes a `keywords` array and a `support` block (issues / source / docs URLs) so the package surfaces correctly on Packagist.
- README: Packagist version and downloads badges, a license badge, an explicit Supported PHP versions section, and a pointer to sandbox-credential onboarding on `developers.paysera.com`. Footer links to Contributing, Security, and License files.

### Changed
- **`firebase/php-jwt` constraint extended from `^6.10` to `^6.10 || ^7.0`**. The 6.x range is flagged by a security advisory (CVE-2025-45769) that surfaces on `composer audit` and blocks installation on PHP 8.4 with the default audit policy. The fix is non-exploitable in this SDK (JWT verification uses asymmetric RSA/EC keys from Paysera Keycloak JWKS, never the symmetric path covered by the CVE), but the advisory blocked external consumers from installing on modern PHP without `--no-audit`. Composer resolves to `firebase/php-jwt` v7.x on PHP 8.0+ — which clears the audit warning — and falls back to the 6.x line on PHP 7.4, where v7 is not installable (v7 requires PHP 8.0+). The `^6.10` floor is preserved so that `--prefer-lowest` builds keep `Firebase\JWT\CachedKeySet`, which `JwtDecoder` depends on. The 7.x line preserves the public API used by `JwtBridge::decode()` and the full test suite passes on both v6.10 and v7.0.5. Note: 7.x enforces a stricter minimum key-size check — keys smaller than 2048 bits are rejected; this does not affect Paysera Keycloak which signs with RSA-2048 or larger.

### Removed
- `.github/` workflows (PHPUnit, PHPStan, PHP-CS-Fixer). Continuous integration runs on the internal GitLab pipeline; the public GitHub repository is a read-only mirror updated on each release tag, so a parallel GitHub Actions pipeline added no value.

## 0.9.1
### Fixed
- **Missing Wise payment-method icon**: bundles `assets/payment-methods/wise.svg` and adds `wise` to `PaymentMethodIconResolver::getSupportedKeys()`, so plugins resolve a `wise` icon URL through the same static mapping as every other supported method. Previously `wise` fell through to an empty URL, leaving the Wise row on the checkout page without a logo.

## 0.9.0
### Added
- **Centralized payment-method icons**: the SDK now ships SVG icons for every supported payment method under `assets/payment-methods/`. `PaymentMethod` exposes the icon URL through the new `getIconUrl(): string` getter. `PaymentMethodNormalizer` prefers an `icon_url` value from the API response and falls back to a static `key → URL` mapping built from a configurable base URL. The API value is validated (`http`/`https` schemes only, control characters and CRLF rejected, malformed URLs fall through to the resolver) so downstream `<img src>` rendering is safe even when upstream values are untrusted. See [docs/PAYMENT_METHOD_ICONS.md](docs/PAYMENT_METHOD_ICONS.md).
- **`SdkFacadeBuilder::setPaymentMethodIconBaseUrl(string $baseUrl)`**: configures the public base URL used by `PaymentMethodIconResolver`. Defaults to `''`, in which case `getIconUrl()` returns `''` and plugins keep full control of icon presentation. Base URLs containing control characters are rejected defensively.
- **`PaymentMethodIconResolver::getSupportedKeys(): string[]`**: exposes the whitelist of payment-method keys that ship with a bundled icon. Single source of truth for plugins and tests; iterating the list is the recommended way to discover available icons.
- **`PaymentMethodIconResolver::getSourceDirectory()`**: static helper returning the absolute path to the SDK's icon directory. Plugins use it to publish icons into their web-accessible asset folder without hardcoding the vendor path.

### Changed
- **`PaymentMethod::__construct`**: extended additively with `string $iconUrl = ''` as the seventh argument. Existing instantiations remain valid; tests and fixtures that build `PaymentMethod` directly do not need updating.
- **`PaymentMethodNormalizer::__construct`**: now requires a `PaymentMethodIconResolver` dependency. Resolves transparently via the DI container for consumers building the SDK through `SdkFacadeBuilder`.

## 0.8.0
### Added
- **Project eligibility flow**: new `$sdkFacade->getProjectEligibilityFacade()->checkProjectEligibility(ProjectEligibilityCheckRequest $request)` returns a unified `ProjectEligibilityResponse` with `status`, `paymentCollectionEnabled`, and `urlVerified`. Status is one of `eligible`, `ineligible`, or `failed_to_check`. The facade itself never throws; transport, normalization, and validation failures are surfaced as `failed_to_check` with `null` flags so plugins can render a deterministic Retry UX. See [docs/PROJECT_ELIGIBILITY.md](docs/PROJECT_ELIGIBILITY.md) for the full response matrix.
- **`ProjectEligibilityEvaluator`**: stateless service that encodes the decision rule (`paymentCollectionEnabled === true && urlVerified === true → eligible`; any other combination → `ineligible`).
- **`PaymentApiClient` GET methods**: `getProjectInfo(): ProjectInfo` and `getProjectWebsites(string $referrer): ProjectWebsiteCollection`. Both compose token-refresh retry on 401 with generic transient-failure retry (5xx, 408, 429, transport) via the new `HttpRetryExecutor`. `getProjectWebsites()` throws `ValidationException` on blank or whitespace-only referrers instead of forwarding a meaningless `referrer=` query parameter.
- **`HttpRetryExecutor` + `SleeperInterface` / `UsleepSleeper`**: generic single-retry layer with a configurable delay (default 1 s). Whitelists `5xx`, `408`, `429`, and transport-level errors (`ApiClientException` with `E_API_CLIENT` code). 401 is intentionally excluded — token refresh handles that path.
- **`executeGetRequest()` helper**: private composition point in `PaymentApiClient` that wraps `executeWithTokenRetry` and `HttpRetryExecutor::execute` around `ApiClient::sendGetRequest`. Used by all GET endpoints.
- **`SleeperInterface` container alias**: `SdkFacadeBuilder::build()` now aliases `SleeperInterface` → `UsleepSleeper` so `HttpRetryExecutor` resolves automatically.

### Changed
- **`PaymentApiClient::getPaymentCollection()`**: now retries on transient HTTP failures (5xx, 408, 429, transport errors) via `HttpRetryExecutor`, in addition to the existing 401 token-refresh retry. Behaviour is observable: latency on transient failures roughly doubles before the call surfaces an exception, but the success and final-failure outcomes are unchanged. POST endpoints (`initiateRefundOrder`, `createPaymentOrder`, `createPaymentLink`) intentionally keep the existing non-retry behaviour — they remain non-idempotent and unsafe to retry without backend `Idempotency-Key` support.
- **`PaymentApiClient::__construct`**: extended additively with `HttpRetryExecutor` as the eighth parameter. Existing call sites that resolve `PaymentApiClient` through `SdkFacadeBuilder` need no changes.
- **`SdkFacade::__construct`**: extended additively with `ProjectEligibility` as the seventh sub-facade. New getter: `getProjectEligibilityFacade()`. Existing six getters are unchanged.

## 0.7.2
### Added
- **Translation Caching**: `TranslationApiClient::getTranslations()` now caches the merged proxy response through the existing PSR-6 pool injected via `SdkFacadeBuilder::setCacheItemPool()`. Cache-hit short-circuits the HTTP request entirely. Default TTL is 24 h; configurable through the new constructor parameter `int $cacheTtlSeconds = 86400`. Cache key: `translations_{pluginNamespace}` (or `translations_` for SDK-only calls), so SDK-only and plugin-merged variants are cached independently.
- **`TranslationApiClient::isDataCached(?string $pluginNamespace)`**: Returns whether the SDK has a cached entry for the given namespace (backed by `CacheItemPoolInterface::hasItem()`).
- **`TranslationApiClient::invalidateDataCache(?string $pluginNamespace)`**: Removes the cached entry for the given namespace (backed by `CacheItemPoolInterface::deleteItem()`), encapsulating the SDK's internal cache-key convention so consumers don't have to hardcode it.
- **Translations facade**: Proxies `isDataCached()` and `invalidateDataCache()` so consumers can manage the throttle window without reaching into the client layer.

### Changed
- **`TranslationApiClient` constructor**: Added required `Psr\Cache\CacheItemPoolInterface $cache` parameter and optional `int $cacheTtlSeconds = 86400`. Consumers using `SdkFacadeBuilder::build()` are unaffected — the existing `cacheItemPool` (default `InMemoryCache`) is wired automatically through the DI container. Direct instantiations need to pass the pool explicitly.

## 0.7.1
### Changed
- **JWKS cache**: extended TTL from 1 hour to 30 days and switched to a cold-start + on-demand rotation strategy. The `/certs` endpoint is hit only on the first validation and whenever an incoming JWT carries a `kid` absent from the cached key set (Keycloak rotation fallback). When a persistent PSR-6 pool is configured via `SdkFacadeBuilder::setCacheItemPool()`, steady-state JWT validation performs zero network calls; the default `InMemoryCache` is request-scoped and still fetches `/certs` per request.
- **JWKS rate limit**: `CachedKeySet` is now instantiated with its built-in rate limit enabled (10 `/certs` fetches per minute per cache pool) to protect against unknown-`kid` amplification when forged JWTs arrive with random `kid` values.
- **Documentation**: `README.md` clarifies that `setCacheItemPool()` must be configured with a persistent PSR-6 pool (Redis / APCu / filesystem) in production — the default `InMemoryCache` is request-scoped and defeats the caching strategy in shared-nothing deployments.

### Security
- The TTL increase from 1 hour to 30 days lengthens the window a compromised Keycloak signing key stays honoured. To force a JWKS refresh during incident response, dedicate an isolated PSR-6 pool to the SDK and clear it as a whole — the `jwks*` cache-key prefix inside `Firebase\JWT\CachedKeySet` is SHA-256-hashed away whenever the composite key exceeds 64 characters (true for every real Paysera JWKS URL), so pattern-based purges like `redis-cli --scan --pattern 'jwks*'` match nothing. See the "Forcing a JWKS refresh" section in `README.md` for the dedicated-pool flush procedure.

## 0.7.0
### Breaking Changes
- **Callbacks Facade**: Removed `verifyCallbackRequest()`, `buildPaymentCallback()`, `getCallbackRequestHeaders()` methods — use `processPaymentCallback()` instead
- **Callbacks Facade**: Removed `getCallbackResponseSuccessMessage()`, `getCallbackResponseSuccessCode()`, `getCallbackResponseVerificationFailedCode()` methods and `CALLBACK_RESPONSE_*` constants
- **Callbacks Facade**: Removed `getSuccessResponse()` method
- **Callbacks Facade**: Constructor signature changed — removed `PaymentApiCallbackHeadersBuilder` dependency, added `CallbackEventValidator` dependency

### Added
- **Callback Event Validation**: `processPaymentCallback()` now validates `event_type` and `event_name` against supported events before processing
- **CallbackEventInterface**: New interface for callback event entities with `getName()` and `getType()` methods
- **CallbackEventValidator**: New validator service checking callback events against supported event types
- **Event Entity**: Added `TYPE_ORDER` and `NAME_AMOUNT_PAID_UPDATED` constants, implements `CallbackEventInterface` and `EnumInterface`
- **UnsupportedCallbackIntegrationException**: Thrown when callback contains an unrecognized event type/name — consumers should catch this and return HTTP 200
- **CallbackVerificationIntegrationException**: Thrown when HMAC signature verification fails

### Migration
```php
// Before (two-step flow)
$facade = $sdk->getCallbacksFacade();
$facade->verifyCallbackRequest($request);
$callback = $facade->buildPaymentCallback($request);

// After (single call)
try {
    $callback = $sdk->getCallbacksFacade()->processPaymentCallback($request);
} catch (UnsupportedCallbackIntegrationException $e) {
    // Unknown event — return HTTP 200, do not process
} catch (CallbackVerificationIntegrationException $e) {
    // HMAC verification failed
} catch (IntegrationException $e) {
    // Build/validation error
}
```

## 0.6.5
### Fixed
- Replace `var_dump` with `json_encode` for improved data serialization in BaseException. Fallback to `print_r` when `json_encode` fails in BaseException

## 0.6.4
### Fixed
- **Payments**: `createPaymentOrder()` and `createPaymentLink()` now propagate the API `error_description` for 4xx client errors instead of showing generic messages like "Payment order creation failed"

## 0.6.3
### Added
- **Translator**: `getTranslations()` now accepts optional `$locale` and `$vocabulary` parameters for filtered access — no need to dig into raw translation structure

## 0.6.2
### Fixed
- Translations facade no longer throws `IntegrationException` on API failure (e.g. network timeout) — returns empty `Translator` instead, preventing fatal errors in consumer applications

## 0.6.1
### Fixed
- Token refresh failure after ~1 hour: `isTokenFresh()` now returns `false` on `JwtValidationException` instead of throwing, allowing normal token refresh flow
- Missing 401 retry in `PaymentApiClient`: added `executeWithTokenRetry()` that refreshes the auth token and retries the request once on HTTP 401

### Changed
- Extracted `PaymentApiHandlerRegistry` to group handler dependencies, reducing `PaymentApiClient` constructor parameters from 10 to 7

## 0.6.0
### Breaking Changes
- **Infrastructure Facade**: Removed `getTranslator()` method and `TranslationApiClient` dependency

### Added
- **Translations Facade**: New dedicated facade for translation operations with `getTranslator(?string $pluginNamespace = null)` method
- **SdkFacade**: Added `getTranslationsFacade()` method
- **TranslationApiClient**: Added optional `$pluginNamespace` parameter to `getTranslations()` method — when provided, fetches SDK + plugin translations and merges them (plugin overrides SDK)

### Migration
```php
// Before
$sdk->getInfrastructureFacade()->getTranslator();

// After
$sdk->getTranslationsFacade()->getTranslator();

// With custom plugin namespace
$sdk->getTranslationsFacade()->getTranslator('custom_plugin');
```

## 0.5.1
### Breaking Changes
- **Authorization Facade**: Removed `getPaymentApiProjectId()` method - project ID is now determined from authentication context

### Added
- **Authorization Facade**: Added `getDecodedToken()` method to retrieve decoded JWT token with `project_id`, `client_id`, and expiration info

## 0.5.0
### Breaking Changes
- **PaymentOrderCreateRequest**: Removed `project_id` from the order creation request body - the API now determines project from authentication context

### Added
- `ResponseValidator` class to centralize HTTP response validation in API client

### Changed
- Extracted payment handlers from `PaymentApiClient` into dedicated handler classes (`PaymentLinkHandler`, `PaymentMethodHandler`, `PaymentOrderHandler`, `RefundOrderHandler`) for better maintainability

## 0.4.0
### Breaking Changes
- **Metadata Entity**: Moved from `Paysera\CheckoutSdk\Entity\PaymentOrder\Metadata` to `Paysera\CheckoutSdk\Entity\Metadata`
- **Metadata**: Added mandatory `referer` field as required constructor parameter
- **Metadata**: Now mandatory (non-nullable) in `PaymentOrderCreateRequest`, `PaymentOrderCreateResponse`, `PaymentLinkCreateRequest`, and `PaymentLinkCreateResponse`

## 0.3.2
### Fixed
- JWT expired token handling: `PaymentApiAuthTokenManager::getJwtDecoded()` now correctly throws `ExpiredException` instead of returning expired token payload
- Error message preservation: `PaymentApiClient::getApiClient()` now includes original exception message in error output
- JWT validation performance: Removed redundant token validation call in `PaymentApiAuthTokenManager::isTokenFresh()`
- JWT exception logging: `JwtDecoder` now only logs generic exceptions, not `JwtValidationException`

### Changed
- Simplified `PaymentApiAuthTokenManager` by removing unused `PaymentApiJwtValidatorInterface` dependency
- Added `ExpiredException` to thrown exceptions documentation in JWT-related interfaces

## 0.3.1
### Changed
- Switched to public translation proxy URL

## 0.3.0
### Added
- JWT token validation support
- PSR-compliant caching with configurable cache pool via `SdkFacadeBuilder::setCacheItemPool()`
- Clock interface support for time-based operations via `SdkFacadeBuilder::setClock()`
- `PaymentApiAuthToken::createFromString()` factory method for easier token creation
- `PaymentApiEnvironment::createFromString()` factory method
- Secure formatter for HTTP logger with automatic sanitization of sensitive data (passwords, tokens, API keys)
- Configurable API client formatter via `SdkFacadeBuilder::setApiClientFormatter()`
- Support for custom body formatters (JSON and form-urlencoded) to sanitize sensitive parameters in request/response logs

### Changed
- **Authorization Facade**: `getPaymentApiProjectId()` now extracts project ID automatically from JWT token without requiring `PaymentApiCredentials` parameter
- HTTP logging now automatically redacts sensitive parameters (client_secret, access_token, password, token) and headers (authorization, set-cookie)

### Breaking Changes
- **PaymentApiAuthToken**: Removed methods `getToken()`, `getTokenType()`, `getScope()`, `getIssuedAt()`, `getExpiresIn()`, `getExpiresAt()`. Use `getAccessToken()` to retrieve token value
- **Authorization::getPaymentApiProjectId()**: Method signature changed from `getPaymentApiProjectId(PaymentApiCredentials $paymentApiCredentials)` to `getPaymentApiProjectId()` - parameter removed

### Dependencies
- Added firebase/php-jwt ^6.10
- Added psr/cache ^1.0 || ^2.0 || ^3.0
- Added psr/clock ^1.0

## 0.2.0
### Added
- Container definition support for dependency injection
- URL override support for local development environments via environment variables
- Makefile for common development tasks
- Local development documentation

### Changed
- Enhanced Container class with fluent API for service definitions
- Updated PaymentApiUrlProvider with configurable base URLs
- Refactored to use parameter names instead of indices in container

## 0.1.0
### Added
- Project skeleton

