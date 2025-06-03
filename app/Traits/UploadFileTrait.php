<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadFileTrait
{
  /**
   * Lưu file vào storage và trả về đường dẫn dạng `/storage/...`
   *
   * @param UploadedFile $file
   * @param string $folder Ví dụ: 'products/cover' hoặc do user chọn
   * @param string $disk Ví dụ: 'public'
   * @return string
   */
  public function uploadFile(UploadedFile $file, string $folder = 'uploads', string $disk = 'public'): string
  {
    $path = $file->store($folder, $disk);
    return '/storage/' . $path;
  }

  /**
   * Lưu nhiều file một lần
   */
  public function uploadMultipleFiles(array $files, string $folder = 'uploads', string $disk = 'public'): array
  {
    return array_map(function ($file) use ($folder, $disk) {
      return $this->uploadFile($file, $folder, $disk);
    }, $files);
  }
}
