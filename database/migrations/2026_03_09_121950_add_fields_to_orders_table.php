<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_code')) {
                $table->string('order_code')->nullable()->after('id');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'shipping_carrier')) {
                $table->string('shipping_carrier')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable()->after('shipping_carrier');
            }
            if (!Schema::hasColumn('orders', 'shipping_fee')) {
                $table->decimal('shipping_fee', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('shipping_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_code', 'payment_status', 'shipping_carrier', 'tracking_code', 'shipping_fee', 'discount']);
        });
    }
};
