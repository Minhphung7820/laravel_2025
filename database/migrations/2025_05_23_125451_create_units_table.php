<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id(); // id đơn vị
            $table->string('name'); // tên đầy đủ, ví dụ: "Kilogram"
            $table->string('abbreviation'); // viết tắt, ví dụ: "kg"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
