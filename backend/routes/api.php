<?php

/**
 * ==========================================================
 * API Routes
 * ==========================================================
 *
 * Definisi rute API untuk aplikasi mobile (React Native).
 * Semua rute menggunakan prefix /api secara otomatis.
 *
 * Rute Publik (tanpa autentikasi):
 * - POST /api/login → Login & dapatkan token
 *
 * Rute Terproteksi (memerlukan token Sanctum):
 * - POST /api/logout → Logout
 * - GET  /api/me → Profil user
 * - Products, Transactions, Suppliers, Categories
 */

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\Route;

// =========================================================
// RUTE PUBLIK (Tidak memerlukan otentikasi)
// =========================================================

/**
 * Login: Mengotentikasi user dan mengembalikan token API.
 * Digunakan oleh halaman login di aplikasi mobile.
 */
Route::post('/login', [AuthController::class, 'login']);

// =========================================================
// RUTE TERPROTEKSI (Memerlukan token Sanctum)
// =========================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Otentikasi ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // --- Produk ---
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('/products/barcode/{code}', [ProductController::class, 'findByBarcode']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // --- Transaksi ---
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    // --- Supplier (Read-only dari mobile) ---
    Route::get('/suppliers', function () {
        return Supplier::where('is_active', true)->orderBy('company_name')->get();
    });

    // --- Kategori (Read-only dari mobile) ---
    Route::get('/categories', function () {
        return Category::orderBy('name')->get();
    });

    // --- Dashboard Stats (Ringkasan untuk widget mobile) ---
    Route::get('/dashboard/stats', function () {
        return response()->json([
            'total_products'      => \App\Models\Product::count(),
            'low_stock_count'     => \App\Models\Product::lowStock()->count(),
            'today_inbound'       => \App\Models\Transaction::where('type', 'inbound')
                                        ->whereDate('transaction_date', today())->count(),
            'today_outbound'      => \App\Models\Transaction::where('type', 'outbound')
                                        ->whereDate('transaction_date', today())->count(),
        ]);
    });

    // --- Dashboard Chart (Grafik 7 hari terakhir) ---
    Route::get('/dashboard/chart', function () {
        $labels = [];
        $inbound = [];
        $outbound = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('D');
            $inbound[] = \App\Models\Transaction::where('type', 'inbound')->whereDate('transaction_date', $date)->count();
            $outbound[] = \App\Models\Transaction::where('type', 'outbound')->whereDate('transaction_date', $date)->count();
        }
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [ 'data' => $inbound, 'color' => '#10b981' ], // green
                [ 'data' => $outbound, 'color' => '#ef4444' ] // red
            ]
        ]);
    });

    // --- Notifikasi (Realtime untuk mobile) ---
    Route::get('/notifications', function (\Illuminate\Http\Request $request) {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'title'      => $notification->data['title'] ?? '',
                    'body'       => $notification->data['body'] ?? '',
                    'icon'       => $notification->data['icon'] ?? null,
                    'iconColor'  => $notification->data['iconColor'] ?? null,
                    'read'       => $notification->read_at !== null,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'notifications'  => $notifications,
            'unread_count'   => $request->user()->unreadNotifications()->count(),
        ]);
    });

    Route::post('/notifications/read-all', function (\Illuminate\Http\Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Semua notifikasi telah ditandai dibaca.']);
    });

    Route::post('/notifications/{id}/read', function (\Illuminate\Http\Request $request, string $id) {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Notifikasi ditandai dibaca.']);
    });
});
