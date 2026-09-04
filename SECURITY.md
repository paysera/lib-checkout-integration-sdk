# Security Policy

Paysera takes the security of `paysera/lib-checkout-integration-sdk` seriously. We appreciate responsible disclosure of vulnerabilities by external researchers and integrators.

## Reporting a vulnerability

**Please do not report security issues through public GitHub issues, pull requests, or discussions.**

Preferred channel: **[GitHub Private Vulnerability Reporting](https://github.com/paysera/lib-checkout-integration-sdk/security/advisories/new)** on the GitHub mirror. The report stays visible only to the maintainers, and the disclosure path integrates with GitHub Security Advisories and CVE assignment.

If GitHub is not an option, send a report to **plugins@paysera.com** with `[SECURITY] lib-checkout-integration-sdk` in the subject.

Include, as much as possible:

- a description of the vulnerability and its potential impact,
- the affected SDK version(s) and the environment where you observed it,
- step-by-step reproduction instructions or a proof-of-concept,
- any suggested mitigation.

You should receive an acknowledgement within **5 business days** and an initial assessment within **30 days**. We will keep you informed about progress and the planned disclosure timeline.

## Supported versions

Security fixes land on the latest minor release. The previous minor release continues to receive security patches for **90 days** after the next minor is published, to give integrators a reasonable upgrade window.

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## Scope

In scope:

- the published SDK source code on Packagist and the GitHub mirror,
- the public API exposed through `SdkFacade` and its sub-facades.

Out of scope (please report through the appropriate Paysera channel instead):

- vulnerabilities in Paysera's hosted services (`paysera.com`, `developers.paysera.com`, the Checkout backend),
- third-party libraries shipped as dependencies — please report those upstream first; we will pick up fixes through normal dependency updates.

## Coordinated disclosure

We follow coordinated disclosure. Once a fix is released, we publish a security advisory on the GitHub repository and a corresponding `CHANGELOG.md` entry under the `### Security` section. We are happy to credit reporters who wish to be acknowledged.
