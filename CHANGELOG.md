# Changelog

All notable changes to this addon are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-06-08

### Changed

- Require `html2img/html2img-php` `^1.0.1`.

## [1.0.0] - 2026-06-07

### Added

- Initial release.
- Automatic Open Graph image generation for entries, rendered by HTML to Image
  in real Chrome from a developer-authored Antlers or Blade view.
- Settings cascade across site, collection and entry.
- Queued generation on save, with input hashing to skip unchanged entries.
- A polished, publishable default template at 1200x630.
- Per-entry controls: template choice, headline and subtitle overrides, a
  custom image upload that bypasses generation, and a disable toggle.
- A Control Panel settings screen, a nav item, and a preview embedded as an
  iframe, plus a per-entry preview and a Generate button on the publish screen.
- A gated preview route for the design loop.
- Standalone `{{ og_image:meta }}` output and an SEO addon integration mode.
- The `og:generate` command for bulk regeneration.
- Optional asset localisation into a Statamic asset container.

[1.0.1]: https://github.com/html2img/statamic-og-images/releases/tag/v1.0.1
[1.0.0]: https://github.com/html2img/statamic-og-images/releases/tag/v1.0.0
