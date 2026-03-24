<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'completed')->update(['status' => 'delivered']);
    }
};
