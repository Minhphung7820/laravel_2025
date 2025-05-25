<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                 => $this->id,
            'product_id'         => $this->product_id,
            'stock_id'           => $this->stock_id,
            'quantity'           => $this->quantity,
            'sell_price'         => $this->sell_price,
            'purchase_price'     => $this->purchase_price,
            'is_sale'            => $this->is_sale,
            'is_product_variant' => $this->is_product_variant,
            'sku'                => $this->sku,
            'barcode'            => $this->barcode,
            'image_url'          => isset($this->variantImages[0]) ? url($this->variantImages[0]->image) : '',
            'attributes'         => collect([
                $this->attributeFirst,
                $this->attributeSecond,
            ])
                ->filter()
                ->map(function ($attribute) {
                    return [
                        'value' => [
                            'id'         => $attribute->id,
                            'title'      => $attribute->title,
                            'variant_id' => $attribute->variant_id,
                        ],
                        'attribute' => $attribute->variant
                            ? [
                                'id'    => $attribute->variant->id,
                                'title' => $attribute->variant->title,
                            ]
                            : null,
                    ];
                })
                ->values(),
        ];
    }
}
