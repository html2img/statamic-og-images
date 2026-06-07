[![html2img — HTML to image API, rendered in real Chrome](https://html2img.com/og-image.png)](https://html2img.com)

# Open Graph Images for Statamic

[![Statamic 6](https://img.shields.io/badge/statamic-6.x-FF269E)](https://statamic.com)
[![Packagist Version](https://img.shields.io/packagist/v/html2img/statamic-og-images)](https://packagist.org/packages/html2img/statamic-og-images)
[![License](https://img.shields.io/packagist/l/html2img/statamic-og-images)](LICENSE)

Automatic Open Graph (social share) images for your Statamic entries, rendered
by the [HTML to Image](https://html2img.com) API in real Chrome. You design the
card as an ordinary Statamic view with full CSS control, and the addon renders
it against each entry, sends the HTML to the API, and stores the returned PNG
URL on the entry.

Because the design is a view in your project, not a fixed template, flexbox,
grid, custom properties, web fonts and anything else you can write in CSS behave
exactly as they do in the browser. Built on the core
[html2img PHP SDK](https://packagist.org/packages/html2img/html2img-php).

## What it does

- Renders a developer-authored Antlers or Blade view into an Open Graph image
  on save, in a queued job, and stores the `i.html2img.com` URL on the entry.
- Resolves settings and template choice through a cascade: a site default, then
  a per-collection override, then a per-entry override.
- Skips regeneration when the render inputs are unchanged, so routine saves do
  not spend credits.
- Outputs the social tags itself, or hands the URL to your existing SEO addon.

## Requirements

- PHP 8.3 or newer
- Statamic 6
- A HTML to Image API key, free from your [dashboard](https://app.html2img.com/register)

## Installation

```bash
composer require html2img/statamic-og-images
```

Add your API key to `.env`. This is the canonical source:

```dotenv
HTML2IMG_API_KEY=your-api-key
```

Publish the config and the default template if you want to customise them:

```bash
php artisan vendor:publish --tag=statamic-og-images-config
php artisan vendor:publish --tag=statamic-og-images-views
```

Open **Tools > Open Graph Images** in the Control Panel to choose which
collections are enabled and set the defaults. See the
[authentication docs](https://html2img.com/docs/authentication) for issuing and
rotating keys.

## Developer setup

### The template

The Open Graph card is a normal Statamic view. The bundled default lives at
`og-images::default`; publish it to `resources/views/vendor/og-images/default.antlers.html`
and edit it, or point the `default_template` setting at a view of your own, for
example `og_image`.

Templates receive the entry's augmented data, so you reference fields the normal
way: `{{ title }}`, `{{ date format="j M Y" }}`, your own fields, and globals.
The addon also passes:

- `og_headline`: the headline override, falling back to the title.
- `og_subtitle`: the subtitle override, if set.
- `site_name` and `site_logo`: from the addon settings.

Treat author, date and an image as optional so one template suits both a blog
post and a plain page:

```antlers
{{ if author_name }}<span>{{ author_name }}</span>{{ /if }}
{{ if image }}<img src="{{ image }}" alt="">{{ /if }}
```

Note that `author` is a reserved Statamic field (the entry's author user), so
name your own byline field something like `author_name`.

### The preview loop

Design in the browser. The addon ships a preview route that renders your
template at the exact configured dimensions, with no API key required, since the
browser renders the same HTML the API does.

- Embedded in the Control Panel: the settings screen and each entry's publish
  screen show a live preview iframe.
- Directly in a browser, for a tight design loop:
  `/cp/og-images/preview` for sample data, or `/cp/og-images/preview?entry=<slug>`
  for a real entry. The route is gated to authenticated Control Panel users.

Once the design looks right, the **Generate** button on the publish screen runs
a real render through the API and shows the actual PNG, the parity check between
the browser preview and the rendered image.

### Local development and public URLs

Renders happen on the HTML to Image servers in real Chrome, so every URL in your
template, web fonts, images and stylesheets, must be reachable from the public
internet. In production your asset and font URLs already are, so the rendered
image matches the browser preview.

On a local development site this is not the case: a locally hosted image, such as
an uploaded Statamic asset on `*.ddev.site` or `*.test`, is invisible to the API
and shows as missing in the rendered PNG, even though your browser preview shows
it. The remedy is to reference publicly hosted assets, or to expose your dev site
with a tunnel (for example `cloudflared tunnel --url ...` or `ddev share`) and
point `APP_URL` at the tunnel while you test. Web fonts loaded from a public CDN,
such as Google Fonts, always work because they are already public.

## Configuration

`config/statamic-og-images.php`:

| Key                | Default                   | Purpose                                         |
| ------------------ | ------------------------- | ----------------------------------------------- |
| `api_key`          | `env('HTML2IMG_API_KEY')` | The API key. The environment is canonical.      |
| `default_template` | `og-images::default`      | The view rendered into the image.               |
| `width` / `height` | `1200` / `630`            | Image dimensions in CSS pixels.                 |
| `dpi`              | `2`                       | Device pixel ratio, 1 to 4.                     |
| `collections`      | `[]`                      | Collections the addon is enabled for.           |
| `overrides`        | `[]`                      | Per-collection template or dimension overrides. |
| `storage`          | `cdn`                     | `cdn` or `asset` (see below).                   |
| `integration`      | `standalone`              | `standalone` or `seo-addon` (see below).        |

The settings can also be managed from the Control Panel. The API key entered
there is stored outside version control, in `resources/addons/`; the environment
variable remains the canonical source.

## Output modes

### Standalone

Emit the tags yourself with the Antlers tag, typically in your `<head>`:

```antlers
{{ og_image:meta }}
```

This outputs `og:image`, `og:image:width`, `og:image:height`, `og:image:alt`,
`og:image:type`, `twitter:card` and `twitter:image`, resolving the cascade
(entry custom image, then generated image, then the site fallback).
`{{ og_image }}` on its own returns just the resolved URL.

### SEO addon integration

If you already run an SEO addon, set `integration` to `seo-addon` and `seo_field`
to the field your addon reads for its social image. The addon writes the
generated URL into that field and stays out of the meta business, so SEO Pro or
Advanced SEO output it as usual.

## Storage modes

- `cdn` (default): stores the `i.html2img.com` URL on the entry. Works with
  static caching, since the frontend outputs a static URL.
- `asset`: downloads the PNG into the configured Statamic asset container and
  stores that asset instead, for sites that do not want a runtime dependency on
  the API.

## Bulk regeneration

After changing a template, regenerate across enabled collections:

```bash
php please og:generate
php please og:generate --collection=blog --force
```

`--force` ignores the input hash and re-renders every entry.

## Asynchronous rendering

Synchronous renders have a 30 second budget. The generation job already runs off
the request cycle, so this rarely matters, but for very large captures the API
also supports [webhook delivery](https://html2img.com/docs/parameters/webhook-url).

## Development

This addon is developed inside a Statamic project using [ddev](https://ddev.com).

```bash
composer install
vendor/bin/pest
vendor/bin/phpstan analyse
vendor/bin/pint --test
npm install && npm run build   # Control Panel assets
```

## Links

[Website](https://html2img.com) · [Documentation](https://html2img.com/docs) · [Laravel guide](https://html2img.com/docs/usage/laravel) · [Templates](https://html2img.com/templates) · [Pricing](https://html2img.com/pricing) · [PHP SDK](https://github.com/html2img/html2img-php)

## Licence

MIT. See [LICENSE](LICENSE).
