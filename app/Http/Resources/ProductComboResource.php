<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductComboResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $parent = $this->parent->toArray();
        $productType = $parent['product_type'] ?? '';
        $product = $parent['product'] ?? [];
        $attr1 = $parent['attribute_first'] ?? [];
        $attr2 = $parent['attribute_second'] ?? [];

        $image = $productType === 'variable'
            ? ($parent['variant_images'][0]['image_url'] ?? env('IMAGE_DEFAULT'))
            : ($product['image_cover_url'] ?? env('IMAGE_DEFAULT'));

        $sku = $productType === 'variable'
            ? ($parent['sku'] ?? '')
            : ($product['sku'] ?? '');

        $productName = $productType !== 'variable'
            ? ($product['name'] ?? '')
            : trim(
                ($product['name'] ?? '') . ' ' .
                    ($attr1['variant']['title'] ?? '') . ' ' .
                    ($attr1['title'] ?? '') . ' ' .
                    ($attr2['variant']['title'] ?? '') . ' ' .
                    ($attr2['title'] ?? '')
            );

        return [
            ...$parent,
            'related_variants' => json_decode($parent['related_variants'] ?? '[]', true),
            'attr1_title'      => $attr1['title'] ?? '',
            'attr2_title'      => $attr2['title'] ?? '',
            'product_name'     => $productName,
            'image'            => $image,
            'sku'              => $sku,
            'parent_id'        => $parent['id'] ?? null,
            'sell_price_combo' => $this->sell_price_combo,
            'quantity_combo'   => $this->quantity_combo,
            'stock_id'         => $this->stock_id,
            'id'               => $this->id,
            'product_type'     => $productType === 'variable' ? $productType : ($product['type'] ?? ''),
        ];
    }
}
