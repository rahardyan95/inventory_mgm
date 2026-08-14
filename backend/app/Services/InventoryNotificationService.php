<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

/**
 * ==========================================================
 * Service: InventoryNotificationService
 * ==========================================================
 *
 * Menangani pengiriman notifikasi database (Filament) ke seluruh pengguna.
 *
 * Notifikasi yang dikirim:
 * 1. Low Stock   → produk dengan stok <= minimum_stock.
 * 2. Supplier    → supplier yang berstatus non-aktif (is_active = false).
 *
 * Setiap notifikasi bersifat deduplikasi: tidak akan dikirim ulang
 * selama notifikasi sejenis untuk entitas yang sama masih berstatus
 * unread pada pengguna yang bersangkutan.
 */
class InventoryNotificationService
{
    /**
     * Kirim notifikasi stok menipis untuk semua produk low stock.
     */
    public function notifyLowStockProducts(): void
    {
        $products = Product::lowStock()->get();

        if ($products->isEmpty()) {
            return;
        }

        $users = User::all();

        foreach ($products as $product) {
            $this->sendToUsers(
                users: $users,
                key: 'low_stock',
                entityId: $product->id,
                title: "Stok Menipis: {$product->name}",
                body: "Stok tersisa {$product->current_stock} (batas minimum: {$product->minimum_stock}).",
                icon: 'heroicon-o-exclamation-triangle',
                color: 'danger',
            );
        }
    }

    /**
     * Kirim notifikasi untuk semua supplier yang berstatus non-aktif.
     */
    public function notifyInactiveSuppliers(): void
    {
        $suppliers = Supplier::where('is_active', false)->get();

        if ($suppliers->isEmpty()) {
            return;
        }

        $users = User::all();

        foreach ($suppliers as $supplier) {
            $this->sendToUsers(
                users: $users,
                key: 'supplier_inactive',
                entityId: $supplier->id,
                title: "Supplier Non-Aktif: {$supplier->company_name}",
                body: 'Supplier ini berstatus non-aktif. Periksa kembali data supplier.',
                icon: 'heroicon-o-building-office-2',
                color: 'warning',
            );
        }
    }

    /**
     * Kirim notifikasi ke daftar user dengan deduplikasi.
     *
     * Notifikasi dilewati (skip) jika sudah ada notifikasi unread
     * dengan key & entity yang sama pada user tersebut.
     *
     * @param  Collection<int, User>  $users
     */
    protected function sendToUsers(Collection $users, string $key, int $entityId, string $title, string $body, string $icon, string $color): void
    {
        foreach ($users as $user) {
            if ($this->hasUnreadNotification($user, $key, $entityId)) {
                continue;
            }

            Notification::make()
                ->title($title)
                ->body($body)
                ->icon($icon)
                ->color($color)
                ->viewData([
                    'key'      => $key,
                    'entityId' => $entityId,
                ])
                ->sendToDatabase($user);
        }
    }

    /**
     * Cek apakah user sudah memiliki notifikasi unread dengan key & entity tertentu.
     */
    protected function hasUnreadNotification(User $user, string $key, int $entityId): bool
    {
        return $user->notifications()
            ->whereNull('read_at')
            ->whereRaw("data->'viewData'->>'key' = ?", [$key])
            ->whereRaw("data->'viewData'->>'entityId' = ?", [(string) $entityId])
            ->exists();
    }
}
