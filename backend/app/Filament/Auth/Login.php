<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

/**
 * Halaman Login Kustom
 *
 * Menambahkan daftar akun demo di bawah form login yang dapat
 * langsung mengisi email & password secara otomatis (auto-fill)
 * untuk memudahkan pengujian.
 */
class Login extends BaseLogin
{
    /**
     * Method Livewire: isi form login dengan kredensial akun demo.
     */
    public function fillDemoAccount(string $email, string $password): void
    {
        $this->form->fill([
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * Tambahkan daftar akun demo setelah form login.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
                $this->getFormContentComponent(),
                $this->getMultiFactorChallengeFormContentComponent(),
                View::make('filament.auth.login-demo-accounts'),
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
            ]);
    }
}
