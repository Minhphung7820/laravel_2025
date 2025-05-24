<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $customers = Category::withCount('variants')->when(
                isset($request['keyword']) && $request['keyword'],
                function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $query->where('title', 'like', '%' . $request['keyword'] . '%');
                    });
                }
            )->orderBy('created_at', 'desc')->paginate($request['limit'] ?? 10);
            return response()->json($customers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $category = Category::create(['title' => $request->title]);

            foreach ($request->variants as $variantData) {
                $variant = $category->variants()->create(['title' => $variantData['title']]);

                foreach ($variantData['attributes'] as $attrData) {
                    $variant->attributes()->create(['title' => $attrData['title']]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Tạo danh mục thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = Category::with('variants.attributes')->findOrFail($id);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $category = Category::with('variants.attributes')->findOrFail($id);
            $category->update(['title' => $request->title]);

            $existingVariantIds = $category->variants->pluck('id')->toArray();
            $newVariantIds = [];

            foreach ($request->variants as $variantData) {
                if (isset($variantData['id'])) {
                    // UPDATE VARIANT
                    $variant = Variant::where('category_id', $category->id)->find($variantData['id']);
                    if ($variant) {
                        $variant->update(['title' => $variantData['title']]);
                        $newVariantIds[] = $variant->id;

                        // Process attributes
                        $existingAttributeIds = $variant->attributes->pluck('id')->toArray();
                        $newAttributeIds = [];

                        foreach ($variantData['attributes'] as $attrData) {
                            if (isset($attrData['id'])) {
                                $attribute = Attribute::where('variant_id', $variant->id)->find($attrData['id']);
                                if ($attribute) {
                                    $attribute->update(['title' => $attrData['title']]);
                                    $newAttributeIds[] = $attribute->id;
                                }
                            } else {
                                $newAttr = $variant->attributes()->create(['title' => $attrData['title']]);
                                $newAttributeIds[] = $newAttr->id;
                            }
                        }

                        // Delete attributes bị xoá
                        $toDeleteAttrs = array_diff($existingAttributeIds, $newAttributeIds);
                        Attribute::whereIn('id', $toDeleteAttrs)->delete();
                    }
                } else {
                    // CREATE NEW VARIANT + ATTRIBUTES
                    $newVariant = $category->variants()->create(['title' => $variantData['title']]);
                    $newVariantIds[] = $newVariant->id;

                    foreach ($variantData['attributes'] as $attrData) {
                        $newVariant->attributes()->create(['title' => $attrData['title']]);
                    }
                }
            }

            // Delete variants bị xoá
            $toDeleteVariants = array_diff($existingVariantIds, $newVariantIds);
            Variant::whereIn('id', $toDeleteVariants)->delete();

            DB::commit();
            return response()->json(['message' => 'Cập nhật danh mục thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
