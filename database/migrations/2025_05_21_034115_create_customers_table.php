<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'unknown'])->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();

            // Quản lý nhóm khách hàng
            $table->string('code')->nullable(); // mã khách nội bộ
            $table->json('tags')->nullable();   // mảng nhãn
            $table->unsignedBigInteger('assigned_user_id')->nullable(); // nhân viên phụ trách

            // Dữ liệu hành vi
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->date('last_order_date')->nullable();
            $table->date('vip_at')->nullable();

            // Phân loại khách
            $table->enum('type', ['individual', 'company'])->default('individual');
            $table->string('source')->nullable(); // Facebook, Zalo, Ads...
            $table->enum('status', ['active', 'inactive', 'blacklist'])->default('active');

            // Social & thông tin phụ
            $table->string('facebook_url')->nullable();
            $table->string('zalo_phone')->nullable();
            $table->string('tax_code')->nullable(); // Mã số thuế nếu là công ty

            // Hạn mức & công nợ (nếu có)
            $table->decimal('debt_amount', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);

            // Ghi chú
            $table->text('note')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // nếu m muốn cho phép khôi phục khách đã xoá
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
