<?php

namespace App\Http\Controllers\Api;

/**
 * ==========================================================
 * Controller: TransactionController (API)
 * ==========================================================
 *
 * Menangani endpoint API untuk transaksi inventaris.
 * Digunakan oleh aplikasi mobile React Native.
 *
 * Endpoints:
 * - GET  /api/transactions     → Riwayat transaksi
 * - POST /api/transactions     → Buat transaksi baru (inbound/outbound/adjustment)
 * - GET  /api/transactions/{id} → Detail transaksi
 */

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TransactionController extends Controller
{
    /**
     * Dependency Injection: InventoryService.
     * Semua logika bisnis stok ditangani oleh service ini.
     */
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    /**
     * Menampilkan riwayat transaksi dengan paginasi dan filter.
     *
     * Query Parameters:
     * - type: Filter berdasarkan tipe (inbound, outbound, adjustment)
     * - date_from / date_to: Filter berdasarkan rentang tanggal
     * - per_page: Jumlah item per halaman (default: 15)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['user', 'supplier', 'items.product']);

        // Filter berdasarkan tipe transaksi
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter berdasarkan rentang tanggal
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        // Urutkan dari yang terbaru
        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json($transactions);
    }

    /**
     * Membuat transaksi baru (inbound / outbound / adjustment).
     *
     * Alur:
     * 1. Validasi input dari client
     * 2. Panggil InventoryService untuk memproses transaksi
     * 3. Stok otomatis diperbarui di dalam service
     * 4. Kembalikan data transaksi yang berhasil dibuat
     *
     * @param Request $request
     * @return JsonResponse Transaksi yang dibuat, atau error 422/500
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi input yang ketat
        $validated = $request->validate([
            // Header transaksi
            'type'              => 'required|in:inbound,outbound,adjustment',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'notes'             => 'nullable|string|max:1000',
            'transaction_date'  => 'nullable|date',

            // Detail items (minimal 1 item)
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes'       => 'nullable|string|max:500',
        ]);

        try {
            // Proses transaksi melalui service layer
            $transaction = $this->inventoryService->createTransaction(
                data: $validated,
                items: $validated['items'],
                userId: $request->user()->id,
            );

            return response()->json([
                'message'     => 'Transaksi berhasil dibuat.',
                'transaction' => $transaction,
            ], 201);

        } catch (InvalidArgumentException $e) {
            // Stok tidak mencukupi (outbound)
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Menampilkan detail satu transaksi beserta item-itemnya.
     *
     * @param int $id ID transaksi
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $transaction = Transaction::with(['user', 'supplier', 'approver', 'items.product'])
            ->findOrFail($id);

        return response()->json($transaction);
    }
}
