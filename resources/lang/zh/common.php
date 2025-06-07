<?php

return [
  'product' => [
    'save_combo_failed'     => '保存组合失败！',
    'create_success'        => '产品添加成功！',
    'not_found'             => '未找到产品',
    'missing_variant'       => '请至少选择一个变体！',
    'missing_stock'         => '请至少选择一个仓库！',
    'update_success'        => '产品更新成功！',
    'variant_create_failed' => '添加变体失败！',
    'create_failed' => '创建产品失败！',
    'update_failed' => '更新产品失败！',
  ],
  'category' => [
    'delete_has_variant' => '无法删除类别，因为有已关联的变体。',
    'delete_in_use' => '无法删除类别，因为它正被商品使用中。',
  ],
  'variant' => [
    'delete_has_attribute' => '无法删除变体，因为存在关联属性。',
    'delete_used_in_stock' => '无法删除变体，因为其属性已被库存使用。',
  ],
  'attribute' => [
    'delete_used' => '无法删除属性，因为它已被库存使用。',
  ],
];
