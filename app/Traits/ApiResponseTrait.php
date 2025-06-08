<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Trả response thành công (200 OK)
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function responseSuccess($data = null, string $message = 'Thành công', int $code = 200): JsonResponse
    {
        return response()->json([
          'success' => true,
          'message' => $message,
          'data'    => $data,
        ], $code);
    }

    /**
     * Trả response thất bại (ví dụ 500, 422, 403)
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors (tuỳ chọn: mảng lỗi hoặc message chi tiết)
     * @return JsonResponse
     */
    public function responseError(string $message = 'Đã xảy ra lỗi', int $code = 500, $errors = null): JsonResponse
    {
        $response = [
          'success' => false,
          'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
