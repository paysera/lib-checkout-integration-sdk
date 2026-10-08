# Contributing

Thank you for taking the time to contribute to `paysera/lib-checkout-integration-sdk`.

## Source of truth

Development happens in Paysera's internal GitLab. The public GitHub repository is a **read-only mirror** that is refreshed on every release tag. Direct pushes to GitHub are rejected, and pull requests are not merged on GitHub: they are for discussion only. Maintainers port accepted changes to GitLab, and they reach GitHub with the next release.

## Reporting bugs

Open a [GitHub issue](https://github.com/paysera/lib-checkout-integration-sdk/issues) with:

- a short description of the problem,
- exact reproduction steps,
- expected vs. actual behaviour,
- PHP version, SDK version, and (if relevant) the e-commerce platform / plugin version,
- relevant log excerpts.

For security vulnerabilities, **do not open a public issue** — follow [SECURITY.md](SECURITY.md) instead.

## Pull requests

1. Fork the repository on GitHub and create a feature branch from `master`.
2. Make focused changes — one logical change per pull request.
3. Add or update tests so the change is covered.
4. Run the local quality gates (see below) and make sure they pass.
5. Add a `CHANGELOG.md` entry under a new `## Unreleased` heading describing the change.
6. Open the pull request against `master` and describe the problem, the solution, and how you verified it.

## Coding standards

- PHP 7.4 or newer; the public API must keep working on every PHP version declared in `composer.json`.
- `declare(strict_types=1);` in every PHP file.
- [PSR-12](https://www.php-fig.org/psr/psr-12/) code style, enforced by PHP-CS-Fixer.
- Static analysis at [PHPStan](https://phpstan.org/) level 8.
- Unit tests with [PHPUnit](https://phpunit.de/) 9.6.
- Public methods that can fail surface user-facing errors as `IntegrationException`; internal errors extend `BaseException`.

## Running quality gates locally

```bash
composer install
composer phpunit
composer analyse
composer format -- --dry-run
```

A full check (formatting + static analysis + tests) is also available through the bundled Docker setup:

```bash
make check
```

## Releases

Maintainers cut releases from `master` by tagging a SemVer version. Public consumers should rely only on tagged releases on Packagist; intermediate commits on `master` are subject to change until they ship in a tag.
