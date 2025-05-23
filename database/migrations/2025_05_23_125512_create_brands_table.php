<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id(); // mã nhãn hiệu
            $table->string('name'); // tên nhãn hiệu
            $table->text('description')->nullable(); // mô tả
            $table->string('logo')->nullable(); // logo (lưu đường dẫn tương đối)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
