<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categories = Category::withCount('variants')
                ->when($request->filled('keyword'), function ($query) use ($request) {
                    $query->where('title', 'like', '%' . $request->keyword . '%');
                })
                ->orderByDesc('created_at')
                ->paginate($request->input('limit', 10));

            return $this->responseSuccess($categories);
        } catch (\Exception $e) {
            return $this->responseError('Lỗi truy vấn danh sách', 500, $e->getMessage());
        }
    }

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
            return $this->responseSuccess($category, 'Tạo danh mục thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseError('Tạo danh mục thất bại', 500, $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = Category::with('variants.attributes')->findOrFail($id);
            return $this->responseSuccess($data);
        } catch (\Exception $e) {
            return $this->responseError('Không tìm thấy danh mục', 404, $e->getMessage());
        }
    }

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
                    $variant = Variant::where('category_id', $category->id)->find($variantData['id']);
                    if ($variant) {
                        $variant->update(['title' => $variantData['title']]);
                        $newVariantIds[] = $variant->id;

                        $existingAttrIds = $variant->attributes->pluck('id')->toArray();
                        $newAttrIds = [];

                        foreach ($variantData['attributes'] as $attrData) {
                            if (isset($attrData['id'])) {
                                $attribute = Attribute::where('variant_id', $variant->id)->find($attrData['id']);
                                if ($attribute) {
                                    $attribute->update(['title' => $attrData['title']]);
                                    $newAttrIds[] = $attribute->id;
                                }
                            } else {
                                $newAttr = $variant->attributes()->create(['title' => $attrData['title']]);
                                $newAttrIds[] = $newAttr->id;
                            }
                        }

                        Attribute::whereIn('id', array_diff($existingAttrIds, $newAttrIds))->delete();
                    }
                } else {
                    $newVariant = $category->variants()->create(['title' => $variantData['title']]);
                    $newVariantIds[] = $newVariant->id;

                    foreach ($variantData['attributes'] as $attrData) {
                        $newVariant->attributes()->create(['title' => $attrData['title']]);
                    }
                }
            }

            Variant::whereIn('id', array_diff($existingVariantIds, $newVariantIds))->delete();

            DB::commit();
            return $this->responseSuccess($category, 'Cập nhật danh mục thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseError('Cập nhật thất bại', 500, $e->getMessage());
        }
    }

    public function getAttributes(Request $request, $id)
    {
        try {
            $variants = Variant::with('attributes')
                ->where('category_id', $id)
                ->get();

            return $this->responseSuccess($variants);
        } catch (\Exception $e) {
            return $this->responseError('Lỗi khi lấy thuộc tính', 500, $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        return $this->responseError('Tính năng đang phát triển', 501);
    }
}
