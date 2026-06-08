<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(protected ApiService $api) {}

    // ── GET /login ───────────────────────────────────────────────────────────
    public function showLogin(): View
    {
        return view('auth.login');
    }

    // ── POST /login ──────────────────────────────────────────────────────────
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'ic_number' => ['required', 'digits:12'],
            'password'  => ['required'],
        ], [
            'ic_number.required' => 'Nombor Kad Pengenalan diperlukan.',
            'ic_number.digits'   => 'Nombor IC mestilah 12 digit tanpa sempang.',
            'password.required'  => 'Kata laluan diperlukan.',
        ]);

        $response = $this->api->post('/auth/login', [
            'ic_number' => $request->ic_number,
            'password'  => $request->password,
        ]);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput($request->only('ic_number'))
                ->with('error', $response['message'] ?? 'Log masuk gagal. Sila cuba semula.');
        }

        $data = $response['data'];

        // Prevent session fixation by rotating session ID after login.
        $request->session()->regenerate();

        // Store in session
        session([
            'api_token' => $data['token'],
            'user'      => $data['user'],
            'roles'     => $data['user']['roles'] ?? [],
        ]);

        if (($data['user']['force_password_change'] ?? false) === true) {
            return redirect()->route('change-password')
                ->with('warning', 'Anda diwajibkan menukar kata laluan sebelum meneruskan.');
        }

        return redirect('/')->with('success', 'Log masuk berjaya. Selamat datang!');
    }

    // ── POST /logout ─────────────────────────────────────────────────────────
    public function logout(Request $request): RedirectResponse
    {
        // Notify API to revoke token
        $this->api->withAuth()->post('/auth/logout');

        // Clear session
        $request->session()->flush();

        return redirect()->route('login')
            ->with('success', 'Anda telah berjaya log keluar.');
    }

    // ── GET /daftar ──────────────────────────────────────────────────────────
    public function showRegister(): View
    {
        return view('auth.register');
    }

    // ── GET /tukar-kata-laluan (guest) ──────────────────────────────────────
    public function showForgotPasswordRequest(): View
    {
        return view('auth.forgot-password-request');
    }

    // ── POST /tukar-kata-laluan/hantar-token ────────────────────────────────
    public function requestPasswordResetToken(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Emel berdaftar diperlukan.',
            'email.email' => 'Format emel tidak sah.',
        ]);

        $response = $this->api->post('/auth/password-reset/request', [
            'email' => $request->email,
        ]);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Permintaan token tidak berjaya dihantar.');
        }

        $query = ['email' => $request->email];

        $debugToken = (string) data_get($response, 'data.debug_token', '');
        if ($debugToken !== '') {
            $query['debug_token'] = $debugToken;
        }

        return redirect()->route('password.reset.verify.form', $query)
            ->with('success', $response['message'] ?? 'Sekiranya emel tersebut wujud dalam sistem, token pengesahan akan dihantar.');
    }

    // ── GET /tukar-kata-laluan/pengesahan ───────────────────────────────────
    public function showForgotPasswordVerification(Request $request): View
    {
        return view('auth.forgot-password-verify', [
            'email' => (string) $request->query('email', old('email', '')),
            'debugToken' => (string) $request->query('debug_token', ''),
        ]);
    }

    // ── POST /tukar-kata-laluan/pengesahan ──────────────────────────────────
    public function verifyPasswordResetToken(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'digits:6'],
        ], [
            'email.required' => 'Emel berdaftar diperlukan.',
            'email.email' => 'Format emel tidak sah.',
            'token.required' => 'Token pengesahan diperlukan.',
            'token.digits' => 'Token pengesahan mesti 6 digit.',
        ]);

        $response = $this->api->post('/auth/password-reset/verify', [
            'email' => $request->email,
            'token' => $request->token,
        ]);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Token pengesahan tidak sah.');
        }

        return redirect()->route('login')
            ->with('success', $response['message'] ?? 'Token berjaya disahkan. Kata laluan sementara telah dihantar ke emel anda.');
    }

    // ── GET /daftar/permohonan ─────────────────────────────────────────────
    public function showRegisterForm(Request $request): View
    {
        $request->validate([
            'jenis_permohonan' => ['required', 'in:pendaftaran_online,pengaktifan_semula_id,penukaran_peranan'],
            'identifier'       => ['required', 'string', 'max:20'],
        ]);

        $identifier = trim((string) $request->query('identifier', ''));
        $ic = preg_match('/^[Tt]/', $identifier) ? strtoupper($identifier) : preg_replace('/\D+/', '', $identifier);
        $noTentera = preg_match('/^[Tt]/', $identifier) ? strtoupper($identifier) : null;

        return view('auth.register-form', [
            'jenis_permohonan' => (string) $request->query('jenis_permohonan'),
            'identifier'       => $identifier,
            'ic_number'        => $ic,
            'no_tentera'       => $noTentera,
            'jabatan'          => [],
        ]);
    }

    // ── GET /daftar/bahagian-unit?jabatan_id={id} ─────────────────────────
    public function registerBahagianUnit(Request $request)
    {
        return response()->json(
            $this->api->get('/ref/bahagian-unit', ['jabatan_id' => $request->jabatan_id])
        );
    }

    // ── POST /daftar ───────────────────────────────────────────────────────
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'jenis_permohonan'             => ['required', 'in:pendaftaran_online,pengaktifan_semula_id,penukaran_peranan'],
            'ic_number'                    => ['required', 'string', 'max:20'],
            'no_tentera'                   => ['nullable', 'string', 'max:20'],
            'name'                         => ['required', 'string', 'max:255'],
            'email'                        => ['required', 'email', 'max:255'],
            'jabatan_bahagian'             => ['nullable', 'string', 'max:255'],
            'bahagian_unit'                => ['nullable', 'string', 'max:255'],
            'telefon_pejabat'              => ['nullable', 'string', 'max:20'],
            'telefon_bimbit'               => ['nullable', 'string', 'max:20'],
            'kategori_permohonan'          => ['required', 'in:agensi,pengguna'],
            'source'                       => ['required', 'in:BTM,JBPM,AGENSI'],
            'peranan'                      => ['required', 'array', 'min:1'],
            'peranan.*'                    => ['in:admin,pendaftar_kontrak,pemilik_projek,admin_sistem,pegawai_undang_undang'],
            'akses_scope'                  => ['nullable', 'string', 'max:100'],
            'password'                     => [
                'required',
                'confirmed',
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).+$/',
            ],
            'lampiran_borang_permohonan'   => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'lampiran_kp_tentera'          => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'lampiran_pas_pekerja'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 12 aksara.',
            'password.regex' => 'Kata laluan mesti mengandungi huruf besar, huruf kecil, nombor dan aksara khas.',
            'lampiran_borang_permohonan.required' => 'Lampiran borang permohonan diperlukan.',
            'lampiran_kp_tentera.required' => 'Lampiran salinan kad pengenalan/tentera diperlukan.',
            'lampiran_pas_pekerja.required' => 'Lampiran pas pekerja diperlukan.',
        ]);

        $payload = $request->only([
            'jenis_permohonan',
            'ic_number',
            'no_tentera',
            'name',
            'email',
            'jabatan_bahagian',
            'bahagian_unit',
            'telefon_pejabat',
            'telefon_bimbit',
            'kategori_permohonan',
            'source',
            'peranan',
            'akses_scope',
            'password',
            'password_confirmation',
        ]);
        $payload['source'] = $payload['kategori_permohonan'] === 'agensi' ? 'AGENSI' : 'BTM';

        $client = Http::baseUrl(config('api.base_url'))
            ->acceptJson()
            ->timeout(45);

        foreach (['lampiran_borang_permohonan', 'lampiran_kp_tentera', 'lampiran_pas_pekerja'] as $fileKey) {
            $file = $request->file($fileKey);
            if ($file) {
                $client = $client->attach(
                    $fileKey,
                    fopen($file->getRealPath(), 'r'),
                    $file->getClientOriginalName(),
                    ['Content-Type' => $file->getMimeType() ?: 'application/octet-stream']
                );
            }
        }

        $response = $client->post('/auth/register', $payload)->json();

        if (! is_array($response) || ! ($response['success'] ?? false)) {
            return back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Permohonan pendaftaran gagal dihantar.');
        }

        return redirect()->route('register')->with('success', $response['message'] ?? 'Permohonan pendaftaran berjaya dihantar.');
    }

    // ── POST /daftar/semakan ───────────────────────────────────────────────
    public function checkRegistrationStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ic_number' => ['required', 'string', 'max:20'],
        ]);

        $response = $this->api->post('/auth/register-check', [
            'ic_number' => $request->ic_number,
        ]);

        if (! ($response['success'] ?? false)) {
            return back()->withInput()->with('error', $response['message'] ?? 'Permohonan tidak ditemui.');
        }

        return back()->withInput()->with('register_status', $response['data'] ?? []);
    }

    // ── GET /tukar-kata-laluan ───────────────────────────────────────────────
    public function showChangePassword(): View
    {
        return view('auth.change-password', [
            'forcePasswordChange' => (bool) (session('user.force_password_change') ?? false),
        ]);
    }

    // ── POST /tukar-kata-laluan ──────────────────────────────────────────────
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'          => ['required'],
            'new_password'              => [
                'required',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            'new_password_confirmation' => ['required'],
        ], [
            'current_password.required'          => 'Kata laluan semasa diperlukan.',
            'new_password.required'              => 'Kata laluan baharu diperlukan.',
            'new_password.min'                   => 'Kata laluan mestilah sekurang-kurangnya 12 aksara.',
            'new_password.confirmed'             => 'Pengesahan kata laluan tidak sepadan.',
            'new_password.regex'                 => 'Kata laluan mesti mengandungi huruf besar, huruf kecil, nombor dan aksara khas.',
            'new_password_confirmation.required' => 'Sila taip semula kata laluan baharu.',
        ]);

        $response = $this->api->withAuth()->post('/auth/change-password', [
            'current_password'              => $request->current_password,
            'new_password'                  => $request->new_password,
            'new_password_confirmation'     => $request->new_password_confirmation,
        ]);

        if (! ($response['success'] ?? false)) {
            return back()->with('error', $response['message'] ?? 'Gagal mengemaskini kata laluan.');
        }

        // Clear session — API revokes all tokens after password change
        $request->session()->flush();

        return redirect()->route('login')
            ->with('success', 'Kata laluan berjaya dikemas kini. Sila log masuk semula.');
    }
}
