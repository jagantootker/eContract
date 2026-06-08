<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestPasswordResetTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\VerifyPasswordResetTokenRequest;
use App\Mail\PasswordResetTokenMail;
use App\Mail\TemporaryPasswordMail;
use App\Models\AuditLog;
use App\Models\PasswordResetVerification;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    private const TOKEN_EXPIRY_MINUTES = 15;

    // ── POST /api/v1/auth/register ───────────────────────────────────────────
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $roleNames = $data['peranan'] ?? [];
        $kategori = $data['kategori_permohonan'] ?? 'pengguna';
        unset(
            $data['peranan'],
            $data['kategori_permohonan'],
            $data['password_confirmation'],
            $data['lampiran_borang_permohonan'],
            $data['lampiran_kp_tentera'],
            $data['lampiran_pas_pekerja']
        );

        $data['kategori_permohonan_agensi'] = $kategori === 'agensi';
        $data['kategori_permohonan_pengguna'] = $kategori === 'pengguna';
        $data['source'] = $kategori === 'agensi' ? 'AGENSI' : ($data['source'] ?? 'BTM');

        $data['no_rujukan_permohonan'] = $this->generateReferenceNumber();
        $data['lampiran_borang_permohonan'] = $request->file('lampiran_borang_permohonan')?->store('permohonan/borang', 'public');
        $data['lampiran_kp_tentera'] = $request->file('lampiran_kp_tentera')?->store('permohonan/kp_tentera', 'public');
        $data['lampiran_pas_pekerja'] = $request->file('lampiran_pas_pekerja')?->store('permohonan/pas_pekerja', 'public');

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = false;
        $data['permohonan_status'] = 'pending';

        $user = User::create($data);

        $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->all();
        if (! empty($roleIds)) {
            $user->roles()->sync($roleIds);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'        => $user->id,
                'ic_number' => $user->ic_number,
                'email'     => $user->email,
                'no_rujukan' => $user->no_rujukan_permohonan,
            ],
            'message' => 'Permohonan berjaya dihantar. No. rujukan anda: ' . $user->no_rujukan_permohonan,
        ], 201);
    }

    // ── POST /api/v1/auth/register-check ────────────────────────────────────
    public function registerCheck(Request $request): JsonResponse
    {
        $request->validate([
            'ic_number' => ['required', 'string', 'max:20'],
        ]);

        $ic = preg_replace('/\D+/', '', (string) $request->ic_number);
        $user = User::where('ic_number', $ic)->orWhere('ic_number', $request->ic_number)->latest()->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan tidak ditemui untuk nombor IC tersebut.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'no_rujukan'        => $user->no_rujukan_permohonan,
                'name'              => $user->name,
                'tarikh_permohonan' => Carbon::parse($user->created_at)->format('d/m/Y'),
                'status'            => $user->permohonan_status ?? 'pending',
            ],
            'message' => 'Status permohonan berjaya diperoleh.',
        ]);
    }

    private function generateReferenceNumber(): string
    {
        do {
            $ref = 'REQ-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('no_rujukan_permohonan', $ref)->exists());

        return $ref;
    }

    // ── POST /api/v1/auth/login ──────────────────────────────────────────────
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('ic_number', $request->ic_number)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Nombor IC atau kata laluan tidak sah.',
            ], 401);
        }

        $isAdminAccount = $user->hasAnyRole(['admin', 'admin_sistem']);
        if (! $isAdminAccount && $user->permohonan_status !== 'diluluskan') {
            return response()->json([
                'success' => false,
                'message' => 'Akaun anda belum diluluskan. Sila tunggu kelulusan pentadbir sebelum log masuk.',
            ], 403);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akaun anda telah disekat. Sila hubungi pentadbir sistem.',
            ], 403);
        }

        // Revoke old tokens (single-session enforcement)
        $user->tokens()->delete();

        $token = $user->createToken('ekontrak-token')->plainTextToken;

        $user->update(['last_login_at' => now()]);

        // Eager-load roles
        $user->load('roles');

        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'               => $user->id,
                    'name'             => $user->name,
                    'ic_number'        => $user->ic_number,
                    'email'            => $user->email,
                    'jabatan_bahagian' => $user->jabatan_bahagian,
                    'bahagian_unit'    => $user->bahagian_unit,
                    'source'           => $user->source,
                    'force_password_change' => (bool) $user->force_password_change,
                    'roles'            => $user->roles->pluck('name'),
                ],
            ],
            'message' => 'Log masuk berjaya.',
        ]);
    }

    // ── POST /api/v1/auth/password-reset/request ────────────────────────────
    public function requestPasswordResetToken(RequestPasswordResetTokenRequest $request): JsonResponse
    {
        $email = Str::lower(trim((string) $request->validated('email')));
        $genericMessage = 'Sekiranya emel tersebut wujud dalam sistem, token pengesahan akan dihantar.';

        Log::info('Password reset token request started.', [
            'email' => $email,
            'ip' => $request->ip(),
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        Log::info('Password reset user lookup completed.', [
            'email' => $email,
            'user_found' => (bool) $user,
        ]);

        if ($user) {
            $token = $this->generateNumericToken();

            PasswordResetVerification::query()->where('email', $email)->delete();

            $verification = PasswordResetVerification::create([
                'email' => $email,
                'token' => $token,
                'expires_at' => now()->addMinutes(self::TOKEN_EXPIRY_MINUTES),
            ]);

            Log::info('Password reset token generated.', [
                'email' => $email,
                'token' => $token,
                'expires_at' => optional($verification->expires_at)?->toDateTimeString(),
                'verification_id' => $verification->id,
            ]);

            try {
                Mail::to($email)->send(new PasswordResetTokenMail($token));
            } catch (Throwable $exception) {
                Log::error('Password reset token email failed to send.', [
                    'email' => $email,
                    'verification_id' => $verification->id,
                    'error' => $exception->getMessage(),
                ]);

                // Allow manual token continuation when SMTP is unavailable if explicitly enabled.
                if ((bool) config('mail.password_reset_allow_fallback', false)) {
                    Log::warning('Password reset using debug token fallback.', [
                        'email' => $email,
                        'verification_id' => $verification->id,
                        'debug_token' => $token,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Pelayan emel tidak dapat dicapai. Token ujian anda: ' . $token,
                        'data' => [
                            'debug_token' => $token,
                            'expires_in_minutes' => self::TOKEN_EXPIRY_MINUTES,
                        ],
                    ]);
                }

                PasswordResetVerification::query()->whereKey($verification->id)->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghantar token. Sila hubungi pentadbir sistem.',
                ], 500);
            }

            AuditLog::record(
                'PASSWORD_RESET_REQUEST',
                'password_reset',
                $user->id,
                ['email' => $email],
                $user->id,
                $request->ip()
            );
        }

        return response()->json([
            'success' => true,
            'message' => $genericMessage,
        ]);
    }

    // ── POST /api/v1/auth/password-reset/verify ─────────────────────────────
    public function verifyPasswordResetToken(VerifyPasswordResetTokenRequest $request): JsonResponse
    {
        $email = Str::lower(trim((string) $request->validated('email')));
        $token = (string) $request->validated('token');

        $verification = PasswordResetVerification::query()
            ->where('email', $email)
            ->where('token', $token)
            ->latest('id')
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'Token pengesahan tidak sah.',
                'errors' => ['token' => ['Token pengesahan tidak sah.']],
            ], 422);
        }

        if ($verification->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'Token pengesahan telah digunakan.',
                'errors' => ['token' => ['Token pengesahan telah digunakan.']],
            ], 422);
        }

        if ($verification->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token pengesahan telah tamat tempoh.',
                'errors' => ['token' => ['Token pengesahan telah tamat tempoh.']],
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Token pengesahan tidak sah.',
                'errors' => ['email' => ['Token pengesahan tidak sah.']],
            ], 422);
        }

        $temporaryPassword = $this->generateTemporaryPassword();

        $user->update([
            'password' => Hash::make($temporaryPassword),
            'force_password_change' => true,
        ]);

        $verification->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        Mail::to($email)->send(new TemporaryPasswordMail($temporaryPassword));

        AuditLog::record(
            'PASSWORD_RESET_TOKEN_VERIFY',
            'password_reset',
            $user->id,
            ['email' => $email],
            $user->id,
            $request->ip()
        );

        AuditLog::record(
            'PASSWORD_RESET_SUCCESS',
            'password_reset',
            $user->id,
            ['email' => $email],
            $user->id,
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Token berjaya disahkan. Kata laluan sementara telah dihantar ke emel anda.',
        ]);
    }

    // ── POST /api/v1/auth/logout ─────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log keluar berjaya.',
        ]);
    }

    // ── POST /api/v1/auth/change-password ────────────────────────────────────
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata laluan semasa tidak betul.',
                'errors'  => ['current_password' => ['Kata laluan semasa tidak betul.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'force_password_change' => false,
        ]);

        AuditLog::record(
            'PASSWORD_CHANGE_SUCCESS',
            'password_change',
            $user->id,
            null,
            $user->id,
            $request->ip()
        );

        // Revoke all tokens after password change — force re-login
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata laluan berjaya dikemas kini. Sila log masuk semula.',
        ]);
    }

    private function generateNumericToken(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function generateTemporaryPassword(): string
    {
        $specials = ['!', '@', '#', '$', '%', '&', '*'];

        return 'Ek' . random_int(10, 99)
            . Str::lower(Str::random(3))
            . Str::upper(Str::random(3))
            . $specials[array_rand($specials)]
            . random_int(100, 999);
    }
}
