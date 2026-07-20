<?php

namespace App\Http\Controllers\Api;

/**
 * ==========================================================
 * Controller: ProductController (API)
 * ==========================================================
 *
 * Menangani endpoint API untuk data produk inventaris.
 * Digunakan oleh aplikasi mobile React Native.
 *
 * Endpoints:
 * - GET    /api/products          → Daftar semua produk (+ filter)
 * - GET    /api/products/{id}     → Detail produk tertentu
 * - GET    /api/products/barcode/{code} → Cari produk berdasarkan barcode
 * - GET    /api/products/low-stock     → Daftar produk stok rendah
 */

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk dengan paginasi dan pencarian.
     *
     * Query Parameters:
     * - search: Cari berdasarkan nama, SKU, atau barcode
     * - category_id: Filter berdasarkan kategori
     * - per_page: Jumlah item per halaman (default: 15)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Filter pencarian: nama, SKU, atau barcode
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%")
                  ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        // Filter berdasarkan kategori
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Paginasi hasil
        $products = $query->orderBy('name')
            ->paginate($request->input('per_page', 15));

        return response()->json($products);
    }

    /**
     * Menampilkan detail satu produk beserta kategorinya.
     *
     * @param int $id ID produk
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json($product);
    }

    /**
     * Mencari produk berdasarkan barcode (digunakan oleh fitur scan kamera).
     *
     * Alur:
     * 1. Aplikasi mobile memindai barcode menggunakan kamera
     * 2. Kode barcode dikirim ke endpoint ini
     * 3. Server mencari produk yang cocok
     * 4. Mengembalikan data produk atau 404 jika tidak ditemukan
     *
     * @param string $code Kode barcode yang dipindai
     * @return JsonResponse Data produk atau error 404
     */
    public function findByBarcode(string $code): JsonResponse
    {
        $product = Product::with('category')
            ->where('barcode', $code)
            ->first();

        if (!$product) {
            return response()->json([
                'message' => "Produk dengan barcode '{$code}' tidak ditemukan.",
            ], 404);
        }

        return response()->json($product);
    }

    /**
     * Menampilkan daftar produk yang stoknya rendah (Low-Stock Alert).
     * Digunakan untuk widget notifikasi di dashboard mobile dan web.
     *
     * @return JsonResponse
     */
    public function lowStock(): JsonResponse
    {
        $products = Product::with('category')
            ->lowStock()            // Scope: current_stock <= minimum_stock
            ->orderBy('current_stock', 'asc')
            ->get();

        return response()->json([
            'count'    => $products->count(),
            'products' => $products,
        ]);
    }
}
