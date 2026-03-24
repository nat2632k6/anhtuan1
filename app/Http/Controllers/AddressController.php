<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Helpers\SecurityHelper;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|max:50|string',
            'phone' => 'required|regex:/^[0-9]{10,11}$/',
            'address' => 'required|max:500|string'
        ]);

        try {
            if ($request->is_default) {
                Address::where('user_id', auth()->id())->update(['is_default' => false]);
            }

            Address::create([
                'user_id' => auth()->id(),
                'label' => SecurityHelper::sanitize($request->label),
                'phone' => preg_replace('/[^0-9]/', '', $request->phone),
                'address' => SecurityHelper::sanitize($request->address),
                'is_default' => $request->is_default ? true : false
            ]);

            return redirect()->back()->with('success', 'Đã thêm địa chỉ mới!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi thêm địa chỉ: ' . $e->getMessage());
        }
    }

    public function setDefault($id)
    {
        $id = (int)$id;
        
        $address = Address::where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$address) {
            return redirect()->back()->with('error', 'Địa chỉ không tồn tại!');
        }

        try {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
            $address->update(['is_default' => true]);
            
            return redirect()->back()->with('success', 'Đã đặt địa chỉ mặc định!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $id = (int)$id;
        
        $address = Address::where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$address) {
            return redirect()->back()->with('error', 'Địa chỉ không tồn tại!');
        }

        try {
            $address->delete();
            return redirect()->back()->with('success', 'Đã xóa địa chỉ!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }
}
