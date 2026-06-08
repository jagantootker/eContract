<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // ── GET /api/v1/users ────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles')
            ->where(function ($q) {
                // Show normal users and approved requests only in Urus Pengguna list.
                $q->whereNull('permohonan_status')
                  ->orWhere('permohonan_status', 'diluluskan');
            })
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('ic_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            });

        $perPage = (int) $request->get('per_page', 5);
        $users   = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $users,
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/users/roles ─────────────────────────────────────────────
    public function rolesIndex(): JsonResponse
    {
        $roles = Role::query()
            ->orderBy('id')
            ->get(['id', 'name', 'label'])
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'label' => $role->label,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $roles,
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/users/permohonan ───────────────────────────────────────
    public function permohonanIndex(Request $request): JsonResponse
    {
        $query = User::with('roles')
            ->whereNotNull('permohonan_status')
            ->when($request->status, fn ($q, $status) => $q->where('permohonan_status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('ic_number', 'like', "%{$search}%")
                        ->orWhere('no_tentera', 'like', "%{$search}%")
                        ->orWhere('no_rujukan_permohonan', 'like', "%{$search}%");
                });
            });

        $perPage = (int) $request->get('per_page', 10);
        $items = $query->latest()->paginate($perPage);

        $items->getCollection()->transform(function (User $user) {
            $requestedRoles = $this->requestedRolesForDisplay($user);

            return [
                'id' => $user->id,
                'no_rujukan_permohonan' => $user->no_rujukan_permohonan,
                'ic_number' => $user->ic_number,
                'no_tentera' => $user->no_tentera,
                'name' => $user->name,
                'email' => $user->email,
                'jenis_permohonan' => $user->jenis_permohonan,
                'akses_scope' => $user->akses_scope,
                'permohonan_status' => $user->permohonan_status,
                'is_active' => $user->is_active,
                'created_at' => optional($user->created_at)?->toDateTimeString(),
                'roles' => $requestedRoles,
                'current_roles' => $user->roles->pluck('name')->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'message' => 'OK',
        ]);
    }

    // ── GET /api/v1/users/permohonan/{id} ──────────────────────────────────
    public function permohonanShow(int $id): JsonResponse
    {
        $user = User::with('roles')->whereNotNull('permohonan_status')->findOrFail($id);

        $fileUrl = function (?string $path): ?string {
            if (! $path) {
                return null;
            }
            return Storage::disk('public')->url($path);
        };

        return response()->json([
            'success' => true,
            'data' => [
                'requested_roles' => $this->requestedRolesForDisplay($user),
                'id' => $user->id,
                'no_rujukan_permohonan' => $user->no_rujukan_permohonan,
                'ic_number' => $user->ic_number,
                'no_tentera' => $user->no_tentera,
                'name' => $user->name,
                'email' => $user->email,
                'jabatan_bahagian' => $user->jabatan_bahagian,
                'bahagian_unit' => $user->bahagian_unit,
                'telefon_pejabat' => $user->telefon_pejabat,
                'telefon_bimbit' => $user->telefon_bimbit,
                'jenis_permohonan' => $user->jenis_permohonan,
                'akses_scope' => $user->akses_scope,
                'permohonan_status' => $user->permohonan_status,
                'is_active' => $user->is_active,
                'created_at' => optional($user->created_at)?->toDateTimeString(),
                'roles' => $user->roles->pluck('name')->values(),
                'lampiran' => [
                    'borang_permohonan' => [
                        'path' => $user->lampiran_borang_permohonan,
                        'url' => $fileUrl($user->lampiran_borang_permohonan),
                    ],
                    'kp_tentera' => [
                        'path' => $user->lampiran_kp_tentera,
                        'url' => $fileUrl($user->lampiran_kp_tentera),
                    ],
                    'pas_pekerja' => [
                        'path' => $user->lampiran_pas_pekerja,
                        'url' => $fileUrl($user->lampiran_pas_pekerja),
                    ],
                ],
            ],
            'message' => 'OK',
        ]);
    }

    // ── PUT /api/v1/users/permohonan/{id}/keputusan ────────────────────────
    public function permohonanKeputusan(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'in:diluluskan,ditolak'],
            'peranan' => ['nullable', 'array'],
            'peranan.*' => ['string', Rule::in(['admin', 'admin_sistem', 'pendaftar_kontrak', 'pemilik_projek', 'pegawai_undang_undang'])],
            'akses_scope' => ['nullable', Rule::in(['PTJ', 'AGENSI', 'KEMENTERIAN'])],
        ]);

        $user = User::whereNotNull('permohonan_status')->findOrFail($id);
        $isApproved = $payload['status'] === 'diluluskan';

        $requestedRoles = array_values($payload['peranan'] ?? $this->pendingRequestedRoles($user));
        $isRoleChange = $user->jenis_permohonan === 'penukaran_peranan';

        $user->update([
            'permohonan_status' => $payload['status'],
            'akses_scope' => $payload['akses_scope'] ?? $user->akses_scope,
            'is_active' => $isRoleChange ? $user->is_active : $isApproved,
            'capaian_peranan' => null,
        ]);

        if ($isRoleChange) {
            if ($isApproved && ! empty($requestedRoles)) {
                $this->syncRoles($user, $requestedRoles);
            }
        } elseif (array_key_exists('peranan', $payload)) {
            $this->syncRoles($user, $payload['peranan'] ?? []);
        }

        $user->load('roles');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'permohonan_status' => $user->permohonan_status,
                'is_active' => $user->is_active,
                'akses_scope' => $user->akses_scope,
                'roles' => $user->roles->pluck('name')->values(),
            ],
            'message' => $isApproved
                ? ($isRoleChange
                    ? 'Permohonan penukaran peranan berjaya diluluskan. Peranan baharu telah diaktifkan.'
                    : 'Permohonan berjaya diluluskan. Akaun pengguna telah diaktifkan.')
                : ($isRoleChange
                    ? 'Permohonan penukaran peranan telah ditolak. Pengguna kekal dengan peranan semasa.'
                    : 'Permohonan telah ditolak. Akaun kekal tidak aktif.'),
        ]);
    }

    private function requestedRolesForDisplay(User $user)
    {
        if ($user->jenis_permohonan === 'penukaran_peranan' && $user->permohonan_status === 'pending') {
            return collect($this->pendingRequestedRoles($user))->values();
        }

        return $user->roles->pluck('name')->values();
    }

    private function pendingRequestedRoles(User $user): array
    {
        return is_array($user->capaian_peranan) ? $user->capaian_peranan : [];
    }

    // ── POST /api/v1/users ───────────────────────────────────────────────────
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data             = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $roles            = $data['roles'] ?? [];
        unset($data['roles'], $data['password_confirmation']);

        $user = User::create($data);

        // Assign roles — exclude 'admin' role from assignment
        $this->syncRoles($user, $roles);

        $user->load('roles');

        return response()->json([
            'success' => true,
            'data'    => $user,
            'message' => 'Pengguna berjaya didaftarkan.',
        ], 201);
    }

    // ── PUT /api/v1/users/{id} ───────────────────────────────────────────────
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validated();
        $roles = $data['roles'] ?? null;
        unset($data['roles'], $data['password_confirmation']);

        // Only update password if provided
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if ($roles !== null) {
            $this->syncRoles($user, $roles);
        }

        $user->load('roles');

        return response()->json([
            'success' => true,
            'data'    => $user,
            'message' => 'Maklumat pengguna berjaya dikemas kini.',
        ]);
    }

    // ── PUT /api/v1/users/{id}/toggle-block ──────────────────────────────────
    public function toggleBlock(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent blocking yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak boleh menyekat akaun anda sendiri.',
            ], 422);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'disekat';

        return response()->json([
            'success' => true,
            'data'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'is_active' => $user->is_active,
            ],
            'message' => "Akaun pengguna berjaya {$status}.",
        ]);
    }

    // ── DELETE /api/v1/users/{id} ────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak boleh memadam akaun anda sendiri.',
            ], 422);
        }

        $user->delete(); // soft delete

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berjaya dipadam.',
        ]);
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    private function syncRoles(User $user, array $roleNames): void
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');
        $user->roles()->sync($roleIds);
    }
}
