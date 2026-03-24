<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\SecurityHelper;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|string',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|regex:/^[0-9]{10,11}$/',
            'address' => 'nullable|max:500|string'
        ]);

        try {
            auth()->user()->update([
                'name' => SecurityHelper::sanitize($request->name),
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone ? preg_replace('/[^0-9]/', '', $request->phone) : null,
                'address' => SecurityHelper::sanitize($request->address)
            ]);

            return redirect()->back()->with('success', 'Đã cập nhật thông tin!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|min:8|confirmed|string',
            'password_confirmation' => 'required|string'
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng!']);
        }

        if ($request->password === $request->current_password) {
            return back()->withErrors(['password' => 'Mật khẩu mới không được trùng với mật khẩu cũ!']);
        }

        try {
            auth()->user()->update(['password' => Hash::make($request->password)]);
            return redirect()->back()->with('success', 'Đã đổi mật khẩu!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi đổi mật khẩu: ' . $e->getMessage());
        }
    }
}
