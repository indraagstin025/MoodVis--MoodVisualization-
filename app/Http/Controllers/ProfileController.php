<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Tambahkan ini jika Anda belum menggunakannya
use Illuminate\Support\Facades\Storage; // Tambahkan ini jika Anda menggunakan Storage facade untuk unlink

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        // Validasi Anda, ini sudah benar.
        // Rule 'sometimes' sudah bagus untuk field opsional.
        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . auth()->id(),
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'class_id' => 'sometimes|nullable|integer|exists:classes,id' // <-- Validasi ini sudah benar
        ]);

        $user = User::find(auth()->id()); // Menggunakan Model User

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // Logika update nama dan email (tidak berubah)
        if ($request->has('name')) {
            $user->name = $request->input('name');
        }
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }

        // --- TAMBAHKAN BARIS INI UNTUK MENYIMPAN CLASS_ID ---
        // Karena `class_id` bisa `nullable` dan `sometimes`,
        // kita perlu secara eksplisit menanganinya.
        // Jika `class_id` dikirim, set nilainya.
        // Jika tidak dikirim sama sekali, nilainya akan tetap.
        // Jika dikirim `null` (misal dari form tanpa pilihan), maka akan diset null.
        if ($request->has('class_id')) {
            $user->class_id = $request->input('class_id');
        } else {
            // Optional: Jika `class_id` tidak dikirim sama sekali, Anda bisa memutuskan
            // untuk tidak mengubahnya atau menyetelnya ke null secara eksplisit jika diperlukan.
            // Untuk kasus ini, 'sometimes' berarti kita biarkan nilai yang ada jika tidak dikirim.
            // Jika Anda ingin menyetelnya ke NULL jika tidak dikirim, gunakan:
            // $user->class_id = null;
        }
        // ----------------------------------------------------

        // Logika upload file
        if ($request->hasFile('photo')) {
            // Logika untuk menghapus foto lama (jika ada)
            if ($user->photo) {
                // PERBAIKAN UNTUK LUMEN (1) - Menggunakan base_path() atau public_path()
                // Lebih disarankan menggunakan Storage facade jika sudah dikonfigurasi,
                // tapi jika langsung ke public folder, app()->basePath('public/') sudah benar.
                $oldPhotoPath = app()->basePath('public/profile/' . $user->photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();

            // PERBAIKAN UNTUK LUMEN (2)
            // Simpan file langsung ke dalam folder 'public/profile'
            $photo->move(app()->basePath('public/profile'), $filename);

            $user->photo = $filename;
        }

        $user->save();

        // Respons JSON Anda (tidak berubah, sudah benar)
        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated',
            // Pastikan Anda memuat ulang (fresh) user atau hanya memilih atribut yang diperbarui
            // Agar respons JSON mencakup perubahan class_id.
            'user' => $user->fresh()->only(['id', 'name', 'email', 'photo_url', 'class_id', 'role']),
        ]);
    }
}
