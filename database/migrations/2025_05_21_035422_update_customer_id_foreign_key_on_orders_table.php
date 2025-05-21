<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Xoá foreign key cũ nếu đang liên kết tới users
            $table->dropForeign(['customer_id']); // 👈 bắt buộc

            // Thêm lại foreign key đúng
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('set null'); // hoặc cascade tùy m
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);

            // Khôi phục nếu cần lại liên kết users
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
