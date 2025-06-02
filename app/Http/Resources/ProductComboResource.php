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
            'related_variants' => json_decode($parent['related_variants'] ?? '[]', true),
            'attr1_title'      => $parent['attribute_first']['title'] ?? '',
            'attr2_title'      => $parent['attribute_second']['title'] ?? '',
        ];
    }
}
