<?php

namespace App\Helpers;

class AuthHelper
{
    public static function user(): ?array
    {
        return session('user');
    }

    public static function token(): ?string
    {
        return session('api_token');
    }

    public static function roles(): array
    {
        return session('roles', []);
    }

    public static function hasRole(string $role): bool
    {
        return in_array($role, static::roles());
    }

    public static function isAdmin(): bool
    {
        return static::hasRole('admin');
    }

    public static function isAdminSistem(): bool
    {
        return static::hasRole('admin_sistem');
    }

    public static function isPendaftar(): bool
    {
        return static::hasRole('pendaftar_kontrak');
    }

    public static function isPemilik(): bool
    {
        return static::hasRole('pemilik_projek');
    }

    public static function isPegawaiUndang(): bool
    {
        return static::hasRole('pegawai_undang_undang');
    }

    public static function userName(): string
    {
        return static::user()['name'] ?? 'Pengguna';
    }

    public static function check(): bool
    {
        return ! empty(static::token());
    }
}
