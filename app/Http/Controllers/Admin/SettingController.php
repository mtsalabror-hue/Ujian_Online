<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create(['nama_sekolah' => 'Nama Sekolah']);
        }
        return view('admin.pengaturan.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::firstOrCreate(
            [],
            ['nama_sekolah' => 'Nama Sekolah']
        );

        $data = $request->except(['_token', '_method']);

        if ($request->hasFile('logo')) {

            $request->validate([
                'logo' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $data['logo'] = $request->file('logo')->store('logo', 'public');
        }

        if ($request->hasFile('ttd_image')) {

            $request->validate([
                'ttd_image' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            if ($setting->ttd_image) {
                Storage::disk('public')->delete($setting->ttd_image);
            }

            $data['ttd_image'] = $request->file('ttd_image')->store('ttd', 'public');
        }

        if ($request->hasFile('stempel_image')) {

            $request->validate([
                'stempel_image' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            if ($setting->stempel_image) {
                Storage::disk('public')->delete($setting->stempel_image);
            }

            $data['stempel_image'] = $request->file('stempel_image')->store('stempel', 'public');
        }

        $setting->update($data);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesi telah berakhir, silakan login kembali.');
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai.');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
