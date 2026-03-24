<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nếu bảng chưa có đầy đủ cột, xóa và tạo lại
        if (Schema::hasTable('product_images')) {
            $columns = Schema::getColumnListing('product_images');
            
            // Nếu thiếu cột image hoặc is_main, xóa bảng và tạo lại
            if (!in_array('image', $columns) || !in_array('is_main', $columns)) {
                Schema::dropIfExists('product_images');
            }
        }

        // Tạo bảng nếu chưa tồn tại
        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('image');
                $table->boolean('is_main')->default(false);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
