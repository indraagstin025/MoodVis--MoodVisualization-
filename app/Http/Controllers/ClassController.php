<?php

namespace App\Http\Controllers;

use App\Models\Classes; // Pastikan Anda sudah membuat model Classes
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClassController extends Controller
{
    /**
     * Menampilkan daftar semua kelas.
     * Endpoint ini bisa digunakan untuk mengisi dropdown di frontend.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $classes = Classes::orderBy('name', 'asc')->get();
        return response()->json([
            'status' => 'success',
            'classes' => $classes,
        ]);
    }

    /**
     * Menyimpan kelas baru yang dibuat oleh Admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:100|unique:classes,name',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        }

        $class = Classes::create([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas baru berhasil dibuat.',
            'class' => $class,
        ], 201);
    }

    /**
     * Menampilkan detail satu kelas spesifik.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $class = Classes::find($id);

        if (!$class) {
            return response()->json(['status' => 'error', 'message' => 'Kelas tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'class' => $class,
        ]);
    }

    /**
     * Mengupdate nama kelas yang sudah ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $class = Classes::find($id);

        if (!$class) {
            return response()->json(['status' => 'error', 'message' => 'Kelas tidak ditemukan.'], 404);
        }

        try {
            $this->validate($request, [
                // Pastikan nama baru unik, kecuali untuk dirinya sendiri
                'name' => 'required|string|max:100|unique:classes,name,' . $id,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
        }

        $class->name = $request->input('name');
        $class->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil diperbarui.',
            'class' => $class,
        ]);
    }

    /**
     * Menghapus sebuah kelas.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $class = Classes::find($id);

        if (!$class) {
            return response()->json(['status' => 'error', 'message' => 'Kelas tidak ditemukan.'], 404);
        }

        // Sebelum menghapus, Anda bisa menambahkan logika untuk memeriksa
        // apakah ada siswa yang masih terdaftar di kelas ini.
        // Jika ada, mungkin Anda ingin mencegah penghapusan atau memindahkan siswa terlebih dahulu.

        $class->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil dihapus.',
        ]);
    }
}