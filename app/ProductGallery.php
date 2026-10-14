<?php
declare(strict_types=1);
namespace Spirit;

/** Selection is a position in approved product images, never an arbitrary media ID. */
final class ProductGallery
{
    public static function selected(mixed $value, int $count): int
    {
        if ((!is_int($value) && !is_string($value)) || !preg_match('/^[1-8]$/D', (string)$value)) {
            return 0;
        }
        $index = (int)$value - 1;
        return $index < max(0, min(8, $count)) ? $index : 0;
    }

    public static function render(array $ids, string $name, string $slug): void
    {
        $ids = array_values($ids);
        $selected = self::selected($_GET['photo'] ?? null, count($ids));
        echo '<div class="product-gallery"><div class="product-main" id="product-main-photo">'
            . PublicSite::image((int)($ids[$selected] ?? 0), $name, 'product-main-image', true) . '</div>';
        if (count($ids) > 1) {
            echo '<div class="product-thumbnails" role="group" aria-label="Product photographs">';
            foreach ($ids as $index => $id) {
                $position = $index + 1;
                $url = app_url('shop/product.php?' . http_build_query(['slug'=>$slug, 'photo'=>$position]));
                $label = 'Photograph ' . $position . ' of ' . count($ids);
                echo '<a class="product-photo" target="_self" href="' . e($url) . '" data-product-photo="'
                    . e(Media::url((int)$id)) . '" data-photo-source="product-photo-source-' . $position
                    . '" data-photo-label="' . e($label) . '" aria-controls="product-main-photo" aria-label="View product photograph '
                    . $position . '"' . ($index === $selected ? ' aria-current="true"' : '') . '>'
                    . PublicSite::image((int)$id, $name . ' — ' . $label, 'product-thumb-image') . '</a>';
                // Template content stays inert: hidden full-size images are not preloaded.
                echo '<template id="product-photo-source-' . $position . '">'
                    . PublicSite::image((int)$id, $name . ' — ' . $label, 'product-main-image', true) . '</template>';
            }
            echo '</div><p class="product-photo-status small muted" role="status" aria-live="polite">Showing photograph '
                . ($selected + 1) . ' of ' . count($ids) . '.</p>';
        }
        echo '</div>';
    }
}
