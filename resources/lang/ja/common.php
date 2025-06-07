<?php

return [
  'product' => [
    'save_combo_failed'     => 'コンボの保存に失敗しました！',
    'create_success'        => '製品が正常に追加されました！',
    'not_found'             => '製品が見つかりません',
    'missing_variant'       => '少なくとも1つのバリアントを選択してください！',
    'missing_stock'         => '少なくとも1つの倉庫を選択してください！',
    'update_success'        => '製品が正常に更新されました！',
    'variant_create_failed' => 'バリアントの追加に失敗しました！',
    'create_failed' => '製品の作成に失敗しました！',
    'update_failed' => '製品の更新に失敗しました！',
  ],
  'category' => [
    'delete_has_variant' => 'バリアントが関連付けられているため、カテゴリを削除できません。',
    'delete_in_use' => '製品で使用されているため、カテゴリを削除できません。',
  ],
  'variant' => [
    'delete_has_attribute' => '属性が関連付けられているため、バリアントを削除できません。',
    'delete_used_in_stock' => '在庫で使用されている属性があるため、バリアントを削除できません。',
  ],
  'attribute' => [
    'delete_used' => '在庫で使用されているため、属性を削除できません。',
  ],
];
