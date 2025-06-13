<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {

        // Validasi Anda, ini sudah benar.
        $this->validate($request, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . auth()->id(),
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
             'class_id' => 'sometimes|nullable|integer|exists:classes,id'
        ]);

        $user = \App\Models\User::find(auth()->id());

        // Logika update nama dan email (tidak berubah)
        if ($request->has('name')) {
            $user->name = $request->input('name');
        }
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }

        // Logika upload file
        if ($request->hasFile('photo')) {
            // Logika untuk menghapus foto lama (jika ada)
            if ($user->photo) {
                // PERBAIKAN UNTUK LUMEN (1)
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
            'user' => $user->only(['id', 'name', 'email', 'photo_url','class_id', 'role']),
        ]);
    }
}
