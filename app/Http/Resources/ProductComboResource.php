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

        return [
            ...$parent,
            'related_variants'       => json_decode($parent['related_variants'] ?? '[]', true),
            'attr1_title'            => $parent['attribute_first']['title'] ?? '',
            'attr2_title'            => $parent['attribute_second']['title'] ?? '',
            'product_name'           => $parent['product']['name'] ?? '',
            'image'                  => $parent['product_type'] === 'variable' ? (isset($parent['variant_images'][0]) ? $parent['variant_images'][0]['image_url'] :  env('IMAGE_DEFAULT')) : ($parent['product']['image_cover_url'] ?? env('IMAGE_DEFAULT')),
            'sku'                    => $parent['product_type'] === 'variable' ? $parent['sku'] : ($parent['product']['sku'] ?? ''),
            'parent_id'              => $parent['id'],
            'sell_price_combo'       => $this->sell_price_combo,
            'quantity_combo'         => $this->quantity_combo,
            'stock_id'               => $this->stock_id,
            'id'                     => $this->id,
        ];
    }
}
