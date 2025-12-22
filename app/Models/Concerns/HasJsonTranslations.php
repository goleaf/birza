<?php

namespace App\Models\Concerns;

/**
 * Minimal replacement for spatie/laravel-translatable.
 *
 * Stores translations in JSON columns like:
 *   {"lt":"...", "en":"..."}
 *
 * - Reading `$model->field` returns the value for current locale (with fallback)
 * - Use `$model->getTranslation('field', 'en')` / `setTranslation` / `setTranslations`
 */
trait HasJsonTranslations
{
    public function isTranslatableAttribute(string $key): bool
    {
        if (!property_exists($this, 'translatable')) {
            return false;
        }

        /** @var array<int, string> $translatable */
        $translatable = (array) $this->translatable;

        return in_array($key, $translatable, true);
    }

    protected function getAttributeValue($key): mixed
    {
        if (is_string($key) && $this->isTranslatableAttribute($key)) {
            return $this->getTranslation($key, app()->getLocale());
        }

        return parent::getAttributeValue($key);
    }

    public function getTranslation(string $key, ?string $locale = null, bool $useFallbackLocale = true): mixed
    {
        $locale = $locale ?: app()->getLocale();
        $translations = $this->getTranslations($key);

        if (array_key_exists($locale, $translations) && $translations[$locale] !== null && $translations[$locale] !== '') {
            return $translations[$locale];
        }

        if ($useFallbackLocale) {
            $fallbackLocale = method_exists($this, 'getFallbackLocale')
                ? $this->getFallbackLocale()
                : config('app.fallback_locale');

            if (
                is_string($fallbackLocale) &&
                $fallbackLocale !== '' &&
                array_key_exists($fallbackLocale, $translations) &&
                $translations[$fallbackLocale] !== null &&
                $translations[$fallbackLocale] !== ''
            ) {
                return $translations[$fallbackLocale];
            }
        }

        foreach ($translations as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function getTranslations(string $key): array
    {
        $raw = $this->getRawTranslationValue($key);

        if (is_array($raw)) {
            return $raw;
        }

        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            // Non-JSON legacy value stored in a JSON column (or cast already applied)
            return [app()->getLocale() => $raw];
        }

        return [];
    }

    public function setTranslation(string $key, string $locale, mixed $value): static
    {
        $translations = $this->getTranslations($key);
        $translations[$locale] = $value;

        $this->attributes[$key] = json_encode(
            $translations,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $this;
    }

    public function setTranslations(string $key, array $translations): static
    {
        $this->attributes[$key] = json_encode(
            $translations,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $this;
    }

    protected function getRawTranslationValue(string $key): mixed
    {
        // Prefer raw original so casts don't interfere
        if (method_exists($this, 'getRawOriginal')) {
            $raw = $this->getRawOriginal($key);
            if ($raw !== null) {
                return $raw;
            }
        }

        return $this->attributes[$key] ?? null;
    }
}




