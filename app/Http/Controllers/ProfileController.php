<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        // --- PERBAIKAN DI SINI ---
        // Mengambil user dengan User::find() agar linter mengenali tipenya
        $user = User::find(auth()->id());

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // --- VALIDASI KONDISIONAL ---
        // Aturan validasi dasar
        $rules = [
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        // Tambahkan aturan untuk class_id HANYA JIKA role pengguna adalah 'murid'
        if ($user->role === 'murid') {
            $rules['class_id'] = 'required|integer|exists:classes,id';
        }

        // Pesan error kustom
        $messages = [
            'class_id.required' => 'Sebagai murid, Anda wajib memilih kelas.',
        ];

        // Jalankan validasi
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // --- AKHIR DARI VALIDASI ---


        // Lanjutkan proses update jika validasi berhasil
        // Gunakan `validated()` untuk keamanan, agar hanya data yang lolos validasi yang digunakan
        $validatedData = $validator->validated();

        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }
        if (isset($validatedData['email'])) {
            $user->email = $validatedData['email'];
        }
        if (isset($validatedData['class_id'])) {
            $user->class_id = $validatedData['class_id'];
        }

        if ($request->hasFile('photo')) {
            // Menggunakan app()->basePath() sesuai permintaan
            $profilePath = app()->basePath('public/profile');

            // Hapus foto lama jika ada
            if ($user->photo && file_exists($profilePath . '/' . $user->photo)) {
                unlink($profilePath . '/' . $user->photo);
            }

            // Pindahkan dan simpan file foto yang baru
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move($profilePath, $filename);

            $user->photo = $filename;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            // Kembalikan data user yang baru agar localStorage di frontend bisa update
            'user' => $user->fresh()->only(['id', 'name', 'email', 'photo_url', 'role', 'class_id']),
        ]);
    }
}