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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('company_province_id')->nullable();
            $table->unsignedBigInteger('company_district_id')->nullable();
            $table->unsignedBigInteger('company_ward_id')->nullable();

            $table->date('founded_at')->nullable();
            $table->string('representative_name');
            $table->string('company_address');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'company_province_id',
                'company_district_id',
                'company_ward_id',
                'founded_at',
                'representative_name',
                'company_address'
            ]);
        });
    }
};
