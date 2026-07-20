<?php

namespace App\Http\Controllers\Api;

/**
 * ==========================================================
 * Controller: AuthController
 * ==========================================================
 *
 * Menangani otentikasi pengguna untuk API (digunakan oleh
 * aplikasi mobile React Native).
 *
 * Endpoints:
 * - POST /api/login  → Login dan dapatkan token Sanctum
 * - POST /api/logout → Revoke token saat ini
 * - GET  /api/me     → Dapatkan profil user yang sedang login
 */

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login dan dapatkan API token (Sanctum).
     *
     * Alur:
     * 1. Validasi input email & password
     * 2. Cari user berdasarkan email
     * 3. Verifikasi password
     * 4. Buat token baru untuk perangkat ini
     * 5. Kembalikan token + data user
     *
     * @param Request $request
     * @return JsonResponse Token dan data user
     *
     * @throws ValidationException Jika kredensial salah
     */
    public function login(Request $request): JsonResponse
    {
        // Validasi input dari client (mobile app)
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string', // Nama perangkat untuk identifikasi token
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Validasi: user tidak ditemukan atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        // Buat token Sanctum baru untuk perangkat ini
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(), // Daftar role dari Spatie
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout: hapus token API yang sedang digunakan.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus token yang sedang digunakan oleh request ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Mendapatkan profil user yang sedang login.
     *
     * @param Request $request
     * @return JsonResponse Data user + roles
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
