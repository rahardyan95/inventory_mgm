<?php

namespace App\Services;

/**
 * ==========================================================
 * Service: InventoryService
 * ==========================================================
 *
 * Kelas layanan inti untuk seluruh logika bisnis inventaris.
 * Bertanggung jawab untuk:
 * - Memproses transaksi inbound (menambah stok)
 * - Memproses transaksi outbound (mengurangi stok)
 * - Memproses stock adjustment / opname
 * - Validasi ketersediaan stok (mencegah stok negatif)
 * - Generate nomor referensi transaksi
 *
 * PENTING: Semua operasi stok HARUS melalui service ini
 * untuk menjamin konsistensi data dan pencatatan audit trail.
 */

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Membuat transaksi baru beserta item-itemnya dan memperbarui stok.
     *
     * Proses:
     * 1. Generate nomor referensi otomatis
     * 2. Simpan header transaksi
     * 3. Simpan detail items
     * 4. Update stok produk berdasarkan tipe transaksi
     *
     * Semua operasi dibungkus dalam DB::transaction() untuk mencegah
     * data korup jika terjadi error di tengah proses.
     *
     * @param array $data Data header transaksi (type, supplier_id, notes, dll.)
     * @param array $items Array berisi detail barang [{product_id, quantity, batch_number, expiry_date}]
     * @param int $userId ID user yang membuat transaksi
     * @return Transaction Objek transaksi yang berhasil dibuat
     *
     * @throws InvalidArgumentException Jika stok tidak mencukupi untuk outbound
     * @throws \Throwable Jika terjadi error database
     */
    public function createTransaction(array $data, array $items, int $userId): Transaction
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            // -------------------------------------------------------
            // LANGKAH 1: Buat header transaksi
            // -------------------------------------------------------
            $transaction = Transaction::create([
                'reference_number'  => Transaction::generateReferenceNumber($data['type']),
                'type'              => $data['type'],
                'user_id'           => $userId,
                'supplier_id'       => $data['supplier_id'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'transaction_date'  => $data['transaction_date'] ?? now()->toDateString(),
                'status'            => 'approved', // Untuk simplisitas, langsung approved
            ]);

            // -------------------------------------------------------
            // LANGKAH 2: Simpan detail items & update stok
            // -------------------------------------------------------
            foreach ($items as $item) {
                // Simpan item transaksi
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'batch_number'   => $item['batch_number'] ?? null,
                    'expiry_date'    => $item['expiry_date'] ?? null,
                    'notes'          => $item['notes'] ?? null,
                ]);

                // Update stok produk berdasarkan tipe transaksi
                $this->updateProductStock(
                    $item['product_id'],
                    $item['quantity'],
                    $data['type']
                );
            }

            // Muat relasi items untuk response
            $transaction->load('items.product');

            return $transaction;
        });
    }

    /**
     * Memperbarui stok produk berdasarkan tipe transaksi.
     *
     * Aturan:
     * - inbound:    stok DITAMBAH (barang masuk ke gudang)
     * - outbound:   stok DIKURANGI (barang keluar dari gudang)
     * - adjustment: stok DISET ULANG ke nilai quantity (hasil opname fisik)
     *
     * @param int $productId ID produk yang stoknya diupdate
     * @param int $quantity Jumlah perubahan stok
     * @param string $type Tipe transaksi: 'inbound', 'outbound', 'adjustment'
     *
     * @throws InvalidArgumentException Jika stok tidak mencukupi (outbound)
     */
    private function updateProductStock(int $productId, int $quantity, string $type): void
    {
        // Gunakan lockForUpdate() untuk mencegah race condition
        // ketika beberapa staf memproses barang yang sama secara bersamaan
        $product = Product::lockForUpdate()->findOrFail($productId);

        switch ($type) {
            case 'inbound':
                // Barang masuk: tambah stok
                $product->current_stock += $quantity;
                break;

            case 'outbound':
                // Barang keluar: validasi ketersediaan stok terlebih dahulu
                if ($product->current_stock < $quantity) {
                    throw new InvalidArgumentException(
                        "Stok tidak mencukupi untuk produk '{$product->name}'. " .
                        "Stok saat ini: {$product->current_stock}, diminta: {$quantity}."
                    );
                }
                $product->current_stock -= $quantity;
                break;

            case 'adjustment':
                // Penyesuaian stok (opname): langsung set ke nilai baru
                $product->current_stock = $quantity;
                break;
        }

        $product->save();
    }
}
