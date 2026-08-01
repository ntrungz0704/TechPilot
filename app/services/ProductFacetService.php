<?php

/**
 * Shared storefront facet contract reader.
 *
 * SQL construction remains in Product so this service stays responsible for
 * category resolution, allowlist validation and UI metadata only.
 */
class ProductFacetService
{
    private static ?array $config = null;

    public static function getConfig(): array
    {
        if (self::$config === null) {
            $loaded = require ROOT_PATH . '/config/product-facets.php';
            self::$config = is_array($loaded) ? $loaded : [];
        }

        return self::$config;
    }

    /**
     * Resolve aliases such as laptop-gaming/man-hinh when they point to one
     * physical category. Parent groups with multiple source categories only
     * receive common filters.
     */
    public static function resolveFacetCategory(string $categorySlug): ?string
    {
        $slug = strtolower(trim($categorySlug));
        if ($slug === '') {
            return null;
        }

        $categories = self::getConfig()['categories'] ?? [];
        if (isset($categories[$slug])) {
            return $slug;
        }

        require_once ROOT_PATH . '/app/services/CatalogGroupService.php';
        $sourceSlugs = CatalogGroupService::resolveSourceSlugs($slug);
        if (count($sourceSlugs) !== 1) {
            return null;
        }

        $sourceSlug = (string)$sourceSlugs[0];
        return isset($categories[$sourceSlug]) ? $sourceSlug : null;
    }

    public static function getFacetDefinitions(string $categorySlug): array
    {
        $resolved = self::resolveFacetCategory($categorySlug);
        if ($resolved === null) {
            return [];
        }

        return self::getConfig()['categories'][$resolved] ?? [];
    }

    public static function getPriceRanges(): array
    {
        return self::getConfig()['common']['price_ranges'] ?? [];
    }

    /**
     * Convert a legacy marketing alias into the public category slug used in
     * generated URLs. The alias-specific defaults are kept as regular facets.
     */
    public static function canonicalCategorySlug(string $categorySlug): string
    {
        $slug = strtolower(trim($categorySlug));
        $alias = self::getConfig()['category_aliases'][$slug] ?? null;

        if (!is_array($alias)) {
            return $slug;
        }

        return (string)($alias['category'] ?? $slug);
    }

    private static function getAliasDefaults(string $categorySlug): array
    {
        $slug = strtolower(trim($categorySlug));
        $alias = self::getConfig()['category_aliases'][$slug] ?? null;
        return is_array($alias) && is_array($alias['defaults'] ?? null)
            ? $alias['defaults']
            : [];
    }

    /**
     * Only return values declared by the current category's facet allowlist.
     */
    public static function normalizeFilters(string $categorySlug, array $input): array
    {
        $normalized = [];
        $aliasDefaults = self::getAliasDefaults($categorySlug);
        foreach (self::getFacetDefinitions($categorySlug) as $param => $definition) {
            $defaultValue = $aliasDefaults[$param] ?? null;
            $rawValue = $input[$param] ?? $defaultValue;
            if (!is_scalar($rawValue)) {
                continue;
            }

            $value = trim((string)$rawValue);
            if ($value === '' || !array_key_exists($value, $definition['options'] ?? [])) {
                continue;
            }

            $normalized[$param] = $value;
        }

        return $normalized;
    }

    public static function getActiveLabels(string $categorySlug, array $filters): array
    {
        $labels = [];
        $definitions = self::getFacetDefinitions($categorySlug);
        foreach ($filters as $param => $value) {
            $definition = $definitions[$param] ?? null;
            $option = $definition['options'][$value] ?? null;
            if (!is_array($definition) || !is_array($option)) {
                continue;
            }

            $labels[$param] = (string)($definition['label'] ?? $param)
                . ': '
                . (string)($option['label'] ?? $value);
        }

        return $labels;
    }
}
