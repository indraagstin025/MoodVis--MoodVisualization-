<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Mendaftarkan pengguna baru.
     * Validasi dilakukan di luar try-catch untuk respons error 422 yang detail.
     */
public function register(Request $request)
{
    // ... (validasi tetap sama)
    $this->validate($request, [
        'name' => 'required|string|max:100',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    try {
        $user = \App\Models\User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'murid', // <--- TAMBAHKAN BARIS INI HANYA UNTUK TES
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully (manual role test).',
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ], 201);

        } catch (\Exception $e) {
            // Menangani error tak terduga (misalnya koneksi database gagal)
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred during registration.',
            ], 500);
        }
    }

    /**
     * Melakukan login pengguna dan mengembalikan token JWT.
     */
    public function login(Request $request)
    {
        // Validasi kredensial. Jika gagal, otomatis mengembalikan JSON error 422.
        $this->validate($request, [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        try {
            $user = User::where('email', $credentials['email'])->first();

            // Cek jika user tidak ada atau password salah
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                 return response()->json([
                    'status' => 'error',
                    'message' => 'Email atau password yang Anda masukkan salah.'
                ], 401);
            }

            // Membuat token dari data user yang sudah diverifikasi
            if (! $token = JWTAuth::fromUser($user)) {
                return response()->json(['status' => 'error', 'message' => 'Gagal membuat token autentikasi.'], 401);
            }

        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada server saat proses login.'], 500);
        }

        // Mengembalikan respons sukses dengan token dan data user (termasuk role)
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            // BENAR: Gunakan variabel $user yang sudah pasti ada
            'user' => $user->only(['id', 'name', 'email', 'photo_url', 'role']),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // Durasi token dalam detik
        ]);
    }

    /**
     * Mendapatkan data pengguna yang sedang terautentikasi.
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth()->user()->only(['id', 'name', 'email', 'photo_url', 'role'])
        ]);
    }

    /**
     * Melakukan logout dengan membatalkan token saat ini.
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed, please try again.'
            ], 500);
        }
    }

    /**
     * Memperbarui token yang sudah ada.
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'status' => 'success',
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed.',
                'details' => $e->getMessage()
            ], 401);
        }
    }
}