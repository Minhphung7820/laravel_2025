<?php

return [
  'product' => [
    'save_combo_failed'     => 'Save Combo failed!',
    'create_success'        => 'Product added successfully!',
    'not_found'             => 'Product not found',
    'missing_variant'       => 'Please select at least one variant!',
    'missing_stock'         => 'Please select at least one warehouse!',
    'update_success'        => 'Product updated successfully!',
    'variant_create_failed' => 'Failed to add variant!',
    'create_failed' => 'Failed to create product!',
    'update_failed' => 'Failed to update product!',
  ],
  'category' => [
    'delete_has_variant' => 'Cannot delete category because it has associated variants.',
    'delete_in_use' => 'Cannot delete category because it is used by a product.'
  ],
  'variant' => [
    'delete_has_attribute' => 'Cannot delete variant because it has associated attributes.',
    'delete_used_in_stock' => 'Cannot delete variant because its attributes are used in stock.'
  ],
  'attribute' => [
    'delete_used' => 'Cannot delete attribute because it is used in stock.'
  ]
];
