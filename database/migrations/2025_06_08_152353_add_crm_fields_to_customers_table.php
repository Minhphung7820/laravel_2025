<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_group_id')->nullable()->after('tags');
            $table->enum('customer_stage', ['new', 'prospect', 'converted', 'inactive', 'lost'])->default('new')->after('vip_at');
            $table->integer('contact_frequency_days')->nullable()->after('customer_stage');
            $table->enum('preferred_contact', ['phone', 'email', 'zalo', 'sms'])->nullable()->after('contact_frequency_days');
            $table->dateTime('last_contacted_at')->nullable()->after('last_order_date');
            $table->unsignedTinyInteger('rating')->nullable()->after('total_spent');
            $table->boolean('marketing_consent')->default(true)->after('note');
            $table->string('referral_code')->nullable()->after('source');

            $table->string('company_name')->nullable()->after('type');
            $table->string('position')->nullable()->after('company_name');
            $table->string('website_url')->nullable()->after('position');
            $table->unsignedInteger('number_of_employees')->nullable()->after('website_url');
            $table->unsignedBigInteger('revenue_estimate')->nullable()->after('number_of_employees');

            $table->string('utm_source')->nullable()->after('source');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
