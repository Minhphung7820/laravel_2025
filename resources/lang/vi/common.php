<?php

return [
  'product' => [
    'save_combo_failed'     => 'Lưu combo thất bại!',
    'create_success'        => 'Thêm sản phẩm thành công!',
    'not_found'             => 'Không tìm thấy sản phẩm',
    'missing_variant'       => 'Vui lòng chọn ít nhất 1 biến thể!',
    'missing_stock'         => 'Vui lòng chọn ít nhất 1 kho!',
    'update_success'        => 'Chỉnh sửa sản phẩm thành công!',
    'variant_create_failed' => 'Lỗi khi thêm biến thể!',
    'create_failed'         => 'Thêm sản phẩm bị lỗi!',
    'update_failed'         => 'Cập nhật sản phẩm thất bại!',
  ],
  'category' => [
    'delete_has_variant' => 'Không thể xóa danh mục vì có biến thể liên kết.',
    'delete_in_use'      => 'Không thể xóa danh mục vì đang được sử dụng bởi sản phẩm.',
  ],
  'variant' => [
    'delete_has_attribute' => 'Không thể xóa biến thể vì có thuộc tính liên kết.',
    'delete_used_in_stock' => 'Không thể xóa biến thể vì thuộc tính đang được sử dụng trong tồn kho.',
  ],
  'attribute' => [
    'delete_used' => 'Không thể xóa thuộc tính vì đang được sử dụng trong kho.',
  ],
];
