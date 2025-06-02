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
            'product_type_text'      => $parent['product']['type_text'] ?? '',
            'status_text'            => $parent['product']['status_text'] ?? '',
            'image'                  => $parent['product']['image_cover_url'] ?? '',
            'sku'                    => $parent['product_type'] === 'variable' ? $parent['sku'] : ($parent['product']['sku'] ?? ''),
            'parent_id'              => $parent['id'],
            'sell_price_combo'       => $this->sell_price_combo,
            'quantity_combo'         => $this->quantity_combo,
            'stock_id'               => $this->stock_id,
            'id'                     => $this->id,
        ];
    }
}
