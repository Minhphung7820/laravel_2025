<?php

return [
  'product' => [
    'save_combo_failed'     => '콤보 저장 실패!',
    'create_success'        => '제품이 성공적으로 추가되었습니다!',
    'not_found'             => '제품을 찾을 수 없습니다',
    'missing_variant'       => '최소한 하나의 변형을 선택하세요!',
    'missing_stock'         => '최소한 하나의 창고를 선택하세요!',
    'update_success'        => '제품이 성공적으로 수정되었습니다!',
    'variant_create_failed' => '변형 추가 실패!',
    'create_failed'         => '제품 생성에 실패했습니다!',
    'update_failed'         => '제품 업데이트에 실패했습니다!',
  ],
  'category' => [
    'delete_has_variant' => '연결된 변형이 있어 카테고리를 삭제할 수 없습니다.',
    'delete_in_use'      => '제품에서 사용 중이므로 카테고리를 삭제할 수 없습니다.',
  ],
  'variant' => [
    'delete_has_attribute' => '속성이 연결되어 있어 변형을 삭제할 수 없습니다.',
    'delete_used_in_stock' => '재고에서 사용 중인 속성이 있어 변형을 삭제할 수 없습니다.',
  ],
  'attribute' => [
    'delete_used' => '재고에서 사용 중이므로 속성을 삭제할 수 없습니다.',
  ],
];
