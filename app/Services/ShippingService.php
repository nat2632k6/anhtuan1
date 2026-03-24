<?php

namespace App\Services;

class ShippingService
{
    private const NEAR_PROVINCES = [
        'quang nam',
        'quang ngai',
        'thua thien hue',
        'da nang',
    ];

    private const FAR_PROVINCES = [
        'ha noi',
        'hai phong',
        'bac ninh',
        'bac giang',
        'lang son',
        'cao bang',
        'ha giang',
        'tuyen quang',
        'yen bai',
        'thai nguyen',
        'phu tho',
        'vinh phuc',
        'bac kan',
        'dien bien',
        'lai chau',
        'son la',
        'hoa binh',
        'thanh hoa',
        'nghe an',
        'ha tinh',
        'quang binh',
        'quang tri',
        'kien giang',
        'ca mau',
        'bac lieu',
        'soc trang',
        'tra vinh',
        'ben tre',
        'tien giang',
        'long an',
        'dong thap',
        'an giang',
        'vinh long',
        'can tho',
        'hau giang',
        'dong nai',
        'binh duong',
        'binh phuoc',
        'tay ninh',
        'ho chi minh',
        'ba ria vung tau',
        'lam dong',
        'dak lak',
        'dak nong',
        'gia lai',
        'kon tum',
    ];

    public function calculateShippingFee(string $address): float
    {
        $province = $this->extractProvince($address);
        
        if ($this->isNearProvince($province)) {
            return (float) rand(20000, 25000);
        }
        
        if ($this->isFarProvince($province)) {
            return (float) rand(30000, 40000);
        }
        
        return 25000;
    }

    private function extractProvince(string $address): string
    {
        $address = strtolower(trim($address));
        
        $keywords = [
            'tinh', 'thanh pho', 'tp', 'tp.', 'tpho', 'tp hcm',
            'ha noi', 'hcm', 'sai gon'
        ];
        
        foreach ($keywords as $keyword) {
            $address = str_replace($keyword, '', $address);
        }
        
        $parts = preg_split('/[,\-\/]/', $address);
        
        foreach (array_reverse($parts) as $part) {
            $part = trim($part);
            if (strlen($part) > 2) {
                return $part;
            }
        }
        
        return '';
    }

    private function isNearProvince(string $province): bool
    {
        $province = strtolower(trim($province));
        
        foreach (self::NEAR_PROVINCES as $nearProvince) {
            if (strpos($province, $nearProvince) !== false || strpos($nearProvince, $province) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function isFarProvince(string $province): bool
    {
        $province = strtolower(trim($province));
        
        foreach (self::FAR_PROVINCES as $farProvince) {
            if (strpos($province, $farProvince) !== false || strpos($farProvince, $province) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function formatFee(float $fee): string
    {
        return number_format($fee) . 'd';
    }
}
