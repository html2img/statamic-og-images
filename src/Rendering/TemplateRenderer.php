<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Rendering;

/**
 * Renders a developer-authored view (Antlers or Blade) into an HTML string.
 *
 * The configured template name is tried as given, then under the addon's
 * `og-images` view namespace, falling back to the bundled default. Statamic
 * registers the Antlers view engine, so `.antlers.html` views render through
 * Laravel's view factory just like Blade.
 */
final class TemplateRenderer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function render(string $template, array $data): string
    {
        return trim((string) view($this->resolve($template), $data)->render());
    }

    public function resolve(string $template): string
    {
        foreach ([$template, 'og-images::'.$template] as $candidate) {
            if (view()->exists($candidate)) {
                return $candidate;
            }
        }

        return 'og-images::default';
    }

    public function exists(string $template): bool
    {
        return view()->exists($template) || view()->exists('og-images::'.$template);
    }
}
