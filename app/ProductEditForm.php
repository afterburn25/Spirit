<?php
declare(strict_types=1);
namespace Spirit;

/** Redisplay authored fields after a rejected save; never alter the saved product. */
final class ProductEditForm
{
    public static function values(?array $product, ?array $submitted = null): array
    {
        $values = ($product['data'] ?? ['name'=>'','description'=>'','size_guide'=>'','category_id'=>0,'image_ids'=>[]])
            + ['slug'=>$product['slug'] ?? '', 'revision'=>$product['revision'] ?? 1];
        if ($submitted === null) { return $values; }
        // Explicit allowlist: never reflect arbitrary submitted credentials,
        // opaque tokens, unexpected fields or an approval checkbox.
        foreach (['name','slug','description','size_guide','category_id','revision'] as $key) {
            if (isset($submitted[$key]) && is_string($submitted[$key])) { $values[$key] = $submitted[$key]; }
        }
        // An absent multiple-select is a deliberate empty selection. An invalid
        // value remains an error and must not execute or reach public content.
        if (!isset($submitted['image_ids'])) { $values['image_ids'] = []; }
        elseif (is_array($submitted['image_ids']) && count($submitted['image_ids']) <= 8) {
            $ids = [];
            foreach ($submitted['image_ids'] as $id) {
                if (!is_string($id) || !ctype_digit($id) || strlen($id) > 10 || (int)$id < 1 || (int)$id > 4294967294) { return $values; }
                $ids[] = (int)$id;
            }
            $values['image_ids'] = array_values(array_unique($ids));
        }
        return $values;
    }
}
