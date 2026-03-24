<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $bannerData = [
            'thoi-trang-nam' => [
                ['title' => 'Thời Trang Nam Cao Cấp', 'desc' => 'Phong cách lịch lãm, sang trọng', 'img' => 'banners/nam-1.jpg'],
                ['title' => 'Xu Hướng Thời Trang Nam 2024', 'desc' => 'Cập nhật xu hướng mới nhất', 'img' => 'banners/nam-2.jpg'],
                ['title' => 'Sale Thời Trang Nam', 'desc' => 'Giảm giá đến 50%', 'img' => 'banners/nam-3.jpg'],
            ],
            'thoi-trang-nu' => [
                ['title' => 'Thời Trang Nữ Thanh Lịch', 'desc' => 'Tôn vinh vẻ đẹp phái đẹp', 'img' => 'banners/nu-1.jpg'],
                ['title' => 'Bộ Sưu Tập Mới', 'desc' => 'Xu hướng thời trang nữ hot nhất', 'img' => 'banners/nu-2.jpg'],
                ['title' => 'Ưu Đãi Thời Trang Nữ', 'desc' => 'Mua sắm thả ga', 'img' => 'banners/nu-3.jpg'],
            ],
            'tre-em' => [
                ['title' => 'Thời Trang Trẻ Em', 'desc' => 'An toàn, thoải mái cho bé yêu', 'img' => 'banners/treem-1.jpg'],
                ['title' => 'Bộ Sưu Tập Trẻ Em', 'desc' => 'Đáng yêu và năng động', 'img' => 'banners/treem-2.jpg'],
                ['title' => 'Sale Thời Trang Trẻ Em', 'desc' => 'Giảm giá hấp dẫn', 'img' => 'banners/treem-3.jpg'],
            ],
            'nuoc-hoa' => [
                ['title' => 'Nước Hoa Cao Cấp', 'desc' => 'Chính hãng 100%', 'img' => 'banners/nuochoa-1.jpg'],
                ['title' => 'Bộ Sưu Tập Nước Hoa', 'desc' => 'Hương thơm quyến rũ', 'img' => 'banners/nuochoa-2.jpg'],
                ['title' => 'Ưu Đãi Nước Hoa', 'desc' => 'Giảm giá đặc biệt', 'img' => 'banners/nuochoa-3.jpg'],
            ],
        ];

        foreach ($bannerData as $slug => $banners) {
            $category = Category::where('slug', $slug)->first();
            if ($category) {
                foreach ($banners as $index => $banner) {
                    if (!Banner::where('image', $banner['img'])->exists()) {
                        Banner::create([
                            'category_id' => $category->id,
                            'title' => $banner['title'],
                            'description' => $banner['desc'],
                            'image' => $banner['img'],
                            'link' => route('category.show', $category->slug),
                            'order' => $index + 1,
                            'is_active' => true
                        ]);
                    }
                }
            }
        }
    }
}
