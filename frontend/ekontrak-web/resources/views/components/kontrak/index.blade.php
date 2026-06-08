@extends('components.layouts.app')

@section('title', 'Senarai Kontrak')
@section('breadcrumb', 'Senarai Kontrak')

@section('content')

@php
    $canEdit = \App\Helpers\AuthHelper::hasRole('pendaftar_kontrak') || \App\Helpers\AuthHelper::isAdmin();
    $data    = $contracts['data'] ?? [];
    $meta    = $contracts['meta'] ?? $contracts;
    $pegawaiItems = is_array($pegawaiList ?? null) ? $pegawaiList : [];
    $pegawaiEmpty = count($pegawaiItems) === 0;
@endphp

<div class="card">
    {{-- Header --}}
    <div class="card-header">
        <div>
            <div class="page-title">Senarai Kontrak</div>
            <div class="page-subtitle">Senarai semua kontrak di bawah pengurusan anda.</div>
        </div>
        <div></div>
    </div>

    <div class="card-body">
        <div class="filter-bar kontrak-filter-row">
            <span class="filter-label">Tahun Mula:</span>
            <div class="year-picker-wrap" id="yearPickerMulaWrap">
                <button type="button" class="year-btn" id="yearBtnMula" onclick="toggleYearPicker('mula')">
                    <span id="yearBtnMulaText">{{ $filters['tahun_mula'] ?? 'Semua' }}</span>
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>
                </button>
                <input type="hidden" id="tahunMula" value="{{ $filters['tahun_mula'] ?? '' }}">
            </div>

            <span class="filter-label">Tahun Tamat:</span>
            <div class="year-picker-wrap" id="yearPickerTamatWrap">
                <button type="button" class="year-btn" id="yearBtnTamat" onclick="toggleYearPicker('tamat')">
                    <span id="yearBtnTamatText">{{ $filters['tahun_tamat'] ?? 'Semua' }}</span>
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>
                </button>
                <input type="hidden" id="tahunTamat" value="{{ $filters['tahun_tamat'] ?? '' }}">
            </div>

            <button type="button" class="btn btn-outline btn-sm" onclick="resetKontrakFilters()" style="padding:0.45rem 0.7rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.93 4.93A10 10 0 1012 2v4M4 4v6h6"/></svg>
                Set Semula
            </button>
            <div class="filter-spacer"></div>
            @if($canEdit)
            <button class="btn btn-primary" onclick="openKontrakModal()" style="padding:0.58rem 1rem;border-radius:12px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kontrak
            </button>
            @endif
        </div>

        <div class="year-popover" id="yearPopover" style="display:none;">
            <div class="year-popover-nav">
                <button type="button" class="year-nav-btn" onclick="shiftYearRange(-9)">«</button>
                <button type="button" class="year-nav-btn" onclick="shiftYearRange(-3)">‹</button>
                <div class="year-range-label" id="yearRangeLabel">2022 - 2030</div>
                <button type="button" class="year-nav-btn" onclick="shiftYearRange(3)">›</button>
                <button type="button" class="year-nav-btn" onclick="shiftYearRange(9)">»</button>
            </div>
            <div class="year-grid" id="yearGrid"></div>
            <button type="button" class="year-current-btn" onclick="selectCurrentYear()">Tahun Semasa</button>
        </div>

        {{-- Table --}}
        <div class="table-wrap" id="kontrakTableWrap">
            @include('components.kontrak._table', ['data' => $data, 'meta' => $meta, 'canEdit' => $canEdit])
        </div>
    </div>
</div>

{{-- ══════════════ MODAL: Tambah / Perincian Kontrak ══════════════ --}}
<div class="modal-overlay" id="modalKontrak">
    <div class="modal" style="max-width:1100px;width:98%;">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon-bubble blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div class="modal-title" id="modalKontrakTitle">Tambah Kontrak Baru</div>
                    <div class="modal-subtitle" id="modalKontrakSubtitle">Isi maklumat kontrak baharu.</div>
                </div>
            </div>
            <button class="modal-close" onclick="closeKontrakModal()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="modal-tabs">
            <button class="modal-tab-btn active" id="ktab-maklumat" onclick="switchKTab('maklumat')">Maklumat Kontrak</button>
            <button class="modal-tab-btn" id="ktab-catatan" onclick="switchKTab('catatan')">Catatan Kontrak</button>
        </div>

        <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
            <form id="formKontrak">
                <input type="hidden" id="kontrakId" name="_kontrak_id" value="">
                <x-validation-summary id="kontrakValidationSummary" title="Sila lengkapkan maklumat yang diperlukan." />

                {{-- ── TAB 1: Maklumat Kontrak ── --}}
                <div id="panel-maklumat">

                    {{-- Maklumat Projek --}}
                    <div class="section-hdr">Maklumat Projek</div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div class="form-group">
                            <x-form.label for="field_no_kontrak" text="No Kontrak" :required="true" />
                            <input type="text" class="form-control" name="no_kontrak" id="field_no_kontrak" placeholder="Contoh: BTMSH1-2025">
                            <div class="invalid-note" id="err_no_kontrak"></div>
                        </div>
                        <div class="form-group">
                            <x-form.label for="field_status_kontrak" text="Status Kontrak" :required="true" />
                            <select class="form-control" name="status_kontrak" id="field_status_kontrak">
                                <option value="DRAF">Draf</option>
                                <option value="DALAM_PELAKSANAAN">Dalam Pelaksanaan</option>
                                <option value="KONTRAK_SELESAI">Kontrak Selesai</option>
                                <option value="EOT">EOT</option>
                            </select>
                            <div class="invalid-note" id="err_status_kontrak"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <x-form.label for="field_tajuk_kontrak" text="Tajuk Kontrak" :required="true" />
                        <textarea class="form-control" name="tajuk_kontrak" id="field_tajuk_kontrak" rows="2" placeholder="Tajuk kontrak..."></textarea>
                        <div class="invalid-note" id="err_tajuk_kontrak"></div>
                    </div>

                    {{-- Syarikat dropdown --}}
                    <div class="form-group">
                        <x-form.label for="field_syarikat_id" text="Nama Syarikat" :required="true" />
                        <select class="form-control" name="syarikat_id" id="field_syarikat_id">
                            <option value="">— Pilih Syarikat —</option>
                            @foreach(($syarikatList ?? []) as $s)
                                <option value="{{ $s['id'] }}">{{ $s['nama_syarikat'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-note" id="err_syarikat_id"></div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div class="form-group">
                            <x-form.label for="field_nilai_kontrak" text="Nilai Kontrak (RM)" :required="true" />
                            <input type="number" class="form-control" name="nilai_kontrak" id="field_nilai_kontrak" placeholder="0.00" step="0.01" min="0">
                            <div class="invalid-note" id="err_nilai_kontrak"></div>
                        </div>
                        <div class="form-group">
                            <x-form.label for="field_kaedah_perolehan" text="Kaedah Perolehan" :required="true" />
                            <select class="form-control" name="kaedah_perolehan">
                                <option value="">— Pilih —</option>
                                <option value="SEBUT HARGA">Sebut Harga</option>
                                <option value="TENDER">Tender</option>
                                <option value="RUNDINGAN TERUS">Rundingan Terus</option>
                                <option value="PEMBELIAN TERUS">Pembelian Terus</option>
                            </select>
                            <div class="invalid-note" id="err_kaedah_perolehan"></div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div class="form-group">
                            <x-form.label for="field_kategori_perolehan" text="Kategori Perolehan" :required="true" />
                            <select class="form-control" name="kategori_perolehan">
                                <option value="">— Pilih —</option>
                                <option value="PERKHIDMATAN">Perkhidmatan</option>
                                <option value="BEKALAN">Bekalan</option>
                                <option value="KERJA">Kerja</option>
                            </select>
                            <div class="invalid-note" id="err_kategori_perolehan"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pihak Berkuasa Melulus</label>
                            <input type="text" class="form-control" name="pihak_berkuasa_melulus_nama" placeholder="Nama pihak berkuasa">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Tarikh Kekuasaan</label>
                            <input type="date" class="form-control" name="pihak_berkuasa_melulus_tarikh">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Diluluskan</label>
                            <input type="date" class="form-control" name="diluluskan_tarikh">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ditandatangani</label>
                            <input type="date" class="form-control" name="ditandatangani_tarikh">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tarikh SST</label>
                            <input type="date" class="form-control" name="tarikh_sst">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div class="form-group">
                            <x-form.label for="field_mula_tarikh" text="Tempoh Kontrak — Mula" :required="true" />
                            <input type="date" class="form-control" name="mula_tarikh" id="field_mula_tarikh">
                            <div class="invalid-note" id="err_mula_tarikh"></div>
                        </div>
                        <div class="form-group">
                            <x-form.label for="field_tamat_tarikh" text="Tempoh Kontrak — Tamat" :required="true" />
                            <input type="date" class="form-control" name="tamat_tarikh" id="field_tamat_tarikh">
                            <div class="invalid-note" id="err_tamat_tarikh"></div>
                        </div>
                    </div>

                    {{-- Pemilik Projek --}}
                    <div class="section-hdr" style="margin-top:1rem;">Pemilik Projek</div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Jabatan / Bahagian</label>
                            <select class="form-control" name="jabatan_id" id="field_jabatan_id" onchange="loadBahagianKontrak(this.value)">
                                <option value="">— Pilih Jabatan —</option>
                                @foreach($jabatan as $jab)
                                    <option value="{{ $jab['id'] }}">{{ $jab['kod'] }} — {{ $jab['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bahagian / Unit</label>
                            <select class="form-control" name="bahagian_unit_id" id="field_bahagian_unit_id">
                                <option value="">— Pilih Unit —</option>
                            </select>
                        </div>
                    </div>

                    {{-- Pegawai Bertanggungjawab dropdown --}}
                    <div class="form-group">
                        <x-form.label for="field_pegawai_bertanggungjawab_id" text="Pegawai Bertanggungjawab" :required="true" />
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                            <select class="form-control" name="pegawai_bertanggungjawab_id" id="field_pegawai_bertanggungjawab_id" onchange="syncPegawaiBertanggungjawabEmail()">
                                <option value="">— Pilih Pegawai —</option>
                                @if($pegawaiEmpty)
                                    <option value="" disabled>Tiada pegawai dijumpai</option>
                                @endif
                                @foreach($pegawaiItems as $u)
                                    <option value="{{ $u['id'] }}" data-email="{{ $u['email'] ?? '' }}">{{ $u['name'] ?? '-' }}</option>
                                @endforeach
                            </select>
                            <div>
                                <input type="email" class="form-control" id="pegawaiEmail" placeholder="Emel pegawai" style="background:#f9fafb;" readonly>
                                <div class="invalid-note" style="color:#ef4444;font-size:0.72rem;">E-mel - ejaan</div>
                            </div>
                        </div>
                        <div class="invalid-note" id="err_pegawai_bertanggungjawab_id"></div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Pegawai Perhubungan 1</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                                <select class="form-control" name="pegawai_perhubungan_1_id" id="field_pegawai_perhubungan_1_id" onchange="syncPerhubunganEmail(1)">
                                    <option value="">— Pilih Pegawai atau TIADA —</option>
                                    @if($pegawaiEmpty)
                                        <option value="" disabled>Tiada pegawai dijumpai</option>
                                    @endif
                                    @foreach($pegawaiItems as $u)
                                        <option value="{{ $u['id'] }}" data-email="{{ $u['email'] ?? '' }}">{{ $u['name'] ?? '-' }}</option>
                                    @endforeach
                                </select>
                                <input type="email" class="form-control" id="perhubungan1Email" placeholder="E-mel" style="background:#f9fafb;" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pegawai Perhubungan 2</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                                <select class="form-control" name="pegawai_perhubungan_2_id" id="field_pegawai_perhubungan_2_id" onchange="syncPerhubunganEmail(2)">
                                    <option value="">— Pilih Pegawai atau TIADA —</option>
                                    @if($pegawaiEmpty)
                                        <option value="" disabled>Tiada pegawai dijumpai</option>
                                    @endif
                                    @foreach($pegawaiItems as $u)
                                        <option value="{{ $u['id'] }}" data-email="{{ $u['email'] ?? '' }}">{{ $u['name'] ?? '-' }}</option>
                                    @endforeach
                                </select>
                                <input type="email" class="form-control" id="perhubungan2Email" placeholder="E-mel" style="background:#f9fafb;" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan Kontrak</label>
                        <textarea class="form-control" name="catatan_kontrak" id="field_catatan_kontrak" rows="3" placeholder="Catatan tambahan..."></textarea>
                    </div>

                </div>

                {{-- ── TAB 2: Catatan ── --}}
                <div id="panel-catatan" style="display:none;">
                    <div id="catatanList">
                        <div style="text-align:center;padding:2rem;color:#9ca3af;">
                            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 0.5rem;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            Tiada catatan diterima.
                        </div>
                    </div>

                    <div id="catatanForm" style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #e5e7eb;display:none;">
                        <div class="section-hdr">Tambah Catatan Baru</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Tahap</label>
                                <input type="text" class="form-control" id="catatanTahap" placeholder="Tahap semasa">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" id="catatanStatus" placeholder="Status semasa">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatanText" rows="3" placeholder="Masukkan catatan..."></textarea>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="submitCatatan()">
                            + Tambah Catatan
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <div class="modal-footer">
            <button class="btn btn-primary" id="btnSimpanKontrak" onclick="submitKontrak()">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan
            </button>
            <button class="btn btn-outline" onclick="closeKontrakModal()">Batal</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ktab-btn now handled by modal-tab-btn in app layout */
    .typeahead-dropdown { position:absolute; top:100%; left:0; right:0; background:white; border:1.5px solid #d1d5db; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:500; max-height:200px; overflow-y:auto; }
    .typeahead-item { padding:0.625rem 0.875rem; font-size:0.85rem; cursor:pointer; border-bottom:1px solid #f3f4f6; }
    .typeahead-item:hover { background:#f0f9ff; }
    .typeahead-item:last-child { border-bottom:none; }

    .kontrak-filter-row {
        position: relative;
        margin-bottom: 1.15rem;
        background: #ffffff;
        border: 1px solid #d6e0ec;
        border-radius: 14px;
        padding: 1.15rem 1.05rem;
    }
    .year-picker-wrap { position: relative; }
    .year-btn {
        display:inline-flex;
        align-items:center;
        gap:0.4rem;
        border:1px solid #dbe3ee;
        border-radius:8px;
        height:34px;
        padding:0 0.65rem;
        background:#f8fbff;
        color:#334155;
        font-size:0.78rem;
        font-weight:600;
        cursor:pointer;
    }
    .year-btn:hover { border-color:#bfdbfe; background:#f0f7ff; }
    .year-popover {
        position: fixed;
        z-index: 620;
        width: 215px;
        background: #fff;
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
        padding: 0.6rem;
    }
    .year-popover-nav { display:flex; align-items:center; gap:0.35rem; margin-bottom:0.5rem; }
    .year-nav-btn {
        width:24px; height:24px; border:0; border-radius:6px; background:#eef3f8; color:#64748b; cursor:pointer;
        font-size:0.82rem; font-weight:700;
    }
    .year-nav-btn:hover { background:#e2e8f0; }
    .year-range-label { flex:1; text-align:center; font-size:0.74rem; font-weight:700; color:#334155; }
    .year-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:0.3rem; }
    .year-cell {
        border:0; border-radius:7px; background:#fff; color:#475569; font-size:0.74rem; font-weight:600;
        padding:0.42rem 0.2rem; cursor:pointer;
    }
    .year-cell:hover { background:#f1f5f9; }
    .year-cell.active { background:#dbeafe; color:#1d4ed8; }
    .year-current-btn {
        margin-top:0.45rem; width:100%; border:0; background:#eff6ff; color:#2563eb; border-radius:7px;
        font-size:0.72rem; font-weight:700; padding:0.45rem 0;
    }
    .th-icon-wrap { display:inline-flex; align-items:center; gap:0.25rem; }

    /* Status badges in table */
    .status-kontrak-selesai  { background:#dcfce7; color:#15803d; }
    .status-dalam-pelaksanaan { background:#dbeafe; color:#1d4ed8; }
    .status-maklumat-tidak-lengkap { background:#fee2e2; color:#b91c1c; }
    .status-draf-kontrak { background:#fef3c7; color:#b45309; }
    .status-draf             { background:#f3f4f6; color:#374151; }
    .status-eot              { background:#f3e8ff; color:#7e22ce; }
    .pill-amber { background:#fef3c7; color:#b45309; }
    .kontrak-status-pill {
        white-space: normal;
        min-width: 88px;
        max-width: 98px;
        justify-content: center;
        text-align: center;
        text-transform: uppercase;
        line-height: 1.15;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        padding: 0.34rem 0.52rem;
        border-radius: 7px;
    }
    .kontrak-no-cell {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        font-size: 0.78rem;
        color: #64748b;
        white-space: nowrap;
        letter-spacing: 0.01em;
    }
    .form-control.is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
        background: #fff7f7;
    }
</style>
@endpush

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const PEGAWAI_COUNT = {{ count($pegawaiItems) }};
    const PEGAWAI_LOAD_ERROR = {{ !empty($pegawaiLoadError) ? 'true' : 'false' }};
    const PEGAWAI_LOAD_MESSAGE = @json($pegawaiLoadMessage ?? 'Gagal memuatkan senarai pegawai');
    let kontrakMode = 'tambah';
    let currentKontrakId = null;
    const REQUIRED_KONTRAK_FIELDS = {
        no_kontrak: 'No kontrak wajib diisi.',
        status_kontrak: 'Status kontrak wajib dipilih.',
        tajuk_kontrak: 'Tajuk kontrak wajib diisi.',
        syarikat_id: 'Nama syarikat wajib dipilih.',
        nilai_kontrak: 'Nilai kontrak wajib diisi.',
        kaedah_perolehan: 'Kaedah perolehan wajib dipilih.',
        kategori_perolehan: 'Kategori perolehan wajib dipilih.',
        mula_tarikh: 'Tarikh mula kontrak wajib diisi.',
        tamat_tarikh: 'Tarikh tamat kontrak wajib diisi.',
        pegawai_bertanggungjawab_id: 'Pegawai bertanggungjawab wajib dipilih.',
    };
    const KONTRAK_SCHEMA = {
        no_kontrak: [
            { type: 'required', message: 'No kontrak wajib diisi.', label: 'No kontrak wajib diisi.' },
            { type: 'pattern', value: '^[A-Z0-9]+-\\d{4}$', message: 'Format no. kontrak tidak sah. Contoh: BTMSH1-2025', label: 'Format no. kontrak tidak sah.' },
        ],
        status_kontrak: [
            { type: 'required', message: 'Status kontrak wajib dipilih.', label: 'Status kontrak wajib dipilih.' },
        ],
        tajuk_kontrak: [
            { type: 'required', message: 'Tajuk kontrak wajib diisi.', label: 'Tajuk kontrak wajib diisi.' },
        ],
        syarikat_id: [
            { type: 'required', message: 'Nama syarikat wajib dipilih.', label: 'Nama syarikat wajib dipilih.' },
        ],
        nilai_kontrak: [
            { type: 'required', message: 'Nilai kontrak wajib diisi.', label: 'Nilai kontrak wajib diisi.' },
            { type: 'numberMin', value: 0, message: 'Nilai kontrak mesti sekurang-kurangnya 0.', label: 'Nilai kontrak mesti sekurang-kurangnya 0.' },
        ],
        kaedah_perolehan: [
            { type: 'required', message: 'Kaedah perolehan wajib dipilih.', label: 'Kaedah perolehan wajib dipilih.' },
        ],
        kategori_perolehan: [
            { type: 'required', message: 'Kategori perolehan wajib dipilih.', label: 'Kategori perolehan wajib dipilih.' },
        ],
        mula_tarikh: [
            { type: 'required', message: 'Tarikh mula kontrak wajib diisi.', label: 'Tarikh mula kontrak wajib diisi.' },
        ],
        tamat_tarikh: [
            { type: 'required', message: 'Tarikh tamat kontrak wajib diisi.', label: 'Tarikh tamat kontrak wajib diisi.' },
            { type: 'dateOrder', startField: 'mula_tarikh', endField: 'tamat_tarikh', allowEqual: true, message: 'Tarikh tamat mesti sama atau selepas tarikh mula.', label: 'Tarikh tamat mesti sama atau selepas tarikh mula.' },
        ],
        diluluskan_tarikh: [
            { type: 'dateOrder', startField: 'diluluskan_tarikh', endField: 'ditandatangani_tarikh', allowEqual: true, message: 'Tarikh ditandatangani mesti sama atau selepas tarikh diluluskan.', label: 'Tarikh ditandatangani mesti sama atau selepas tarikh diluluskan.' },
        ],
        pegawai_bertanggungjawab_id: [
            { type: 'required', message: 'Pegawai bertanggungjawab wajib dipilih.', label: 'Pegawai bertanggungjawab wajib dipilih.' },
        ],
    };

    // ── Modal ────────────────────────────────────────────────────────────────
    function openKontrakModal(id = null) {
        resetKontrakForm();
        switchKTab('maklumat');
        if (id) {
            kontrakMode = 'kemaskini';
            currentKontrakId = id;
            loadKontrak(id);
        } else {
            kontrakMode = 'tambah';
            currentKontrakId = null;
            document.getElementById('modalKontrakTitle').textContent = 'Tambah Kontrak Baru';
            document.getElementById('modalKontrakSubtitle').textContent = 'Isi maklumat kontrak baharu.';
            document.getElementById('catatanForm').style.display = 'none';
        }
        document.getElementById('modalKontrak').classList.add('open');
    }

    function closeKontrakModal() {
        document.getElementById('modalKontrak').classList.remove('open');
    }

    document.querySelectorAll('.modal-overlay').forEach(o => {
        o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
    });

    // ── Tab switching ────────────────────────────────────────────────────────
    function switchKTab(tab) {
        ['maklumat','catatan'].forEach(t => {
            document.getElementById('ktab-' + t).classList.toggle('active', t === tab);
            document.getElementById('panel-' + t).style.display = t === tab ? 'block' : 'none';
        });
        if (tab === 'catatan' && currentKontrakId) loadCatatan(currentKontrakId);
    }

    // ── Load existing contract ────────────────────────────────────────────────
    async function loadKontrak(id) {
        const res  = await fetch(`/kontrak/${id}`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const k    = json.data ?? {};

        const dateFields = new Set([
            'mula_tarikh',
            'tamat_tarikh',
            'pihak_berkuasa_melulus_tarikh',
            'diluluskan_tarikh',
            'ditandatangani_tarikh',
            'tarikh_sst',
        ]);

        function toDateInputValue(value) {
            if (!value) return '';

            if (typeof value === 'object' && value.date) {
                value = value.date;
            }

            const str = String(value);
            const direct = str.match(/^(\d{4}-\d{2}-\d{2})/);
            if (direct) return direct[1];

            const parsed = new Date(str);
            if (!Number.isNaN(parsed.getTime())) {
                return parsed.toISOString().slice(0, 10);
            }

            return '';
        }

        document.getElementById('modalKontrakTitle').textContent    = 'Perincian Kontrak';
        document.getElementById('modalKontrakSubtitle').textContent = k.no_kontrak ?? '';
        document.getElementById('kontrakId').value = k.id ?? '';

        // Fill fields
        const fields = ['no_kontrak','tajuk_kontrak','nilai_kontrak','catatan_kontrak',
                        'mula_tarikh','tamat_tarikh','pihak_berkuasa_melulus_nama',
                        'pihak_berkuasa_melulus_tarikh','diluluskan_tarikh','ditandatangani_tarikh','tarikh_sst'];
        fields.forEach(f => {
            const el = document.getElementById('field_' + f) || document.querySelector(`[name="${f}"]`);
            if (!el) return;
            el.value = dateFields.has(f) ? toDateInputValue(k[f]) : (k[f] ?? '');
        });

        // Selects
        if (k.status_kontrak) setSelectValue('field_status_kontrak', k.status_kontrak);
        if (k.kaedah_perolehan) setSelectValue('[name="kaedah_perolehan"]', k.kaedah_perolehan, true);
        if (k.kategori_perolehan) setSelectValue('[name="kategori_perolehan"]', k.kategori_perolehan, true);

        // Syarikat
        if (k.syarikat) {
            ensureOptionExists('field_syarikat_id', k.syarikat.id, k.syarikat.nama_syarikat ?? '');
            document.getElementById('field_syarikat_id').value = k.syarikat.id ?? '';
        }

        // Jabatan
        if (k.jabatan_id) {
            document.getElementById('field_jabatan_id').value = k.jabatan_id;
            await loadBahagianKontrak(k.jabatan_id, k.bahagian_unit_id);
        }

        // Pegawai
        if (k.pegawai_bertanggungjawab) {
            ensureOptionExists('field_pegawai_bertanggungjawab_id', k.pegawai_bertanggungjawab.id, k.pegawai_bertanggungjawab.name ?? '', k.pegawai_bertanggungjawab.email ?? '');
            document.getElementById('field_pegawai_bertanggungjawab_id').value = k.pegawai_bertanggungjawab.id ?? '';
            syncPegawaiBertanggungjawabEmail();
        }
        if (k.pegawai_perhubungan_1) {
            ensureOptionExists('field_pegawai_perhubungan_1_id', k.pegawai_perhubungan_1.id, k.pegawai_perhubungan_1.name ?? '', k.pegawai_perhubungan_1.email ?? '');
            document.getElementById('field_pegawai_perhubungan_1_id').value = k.pegawai_perhubungan_1.id ?? '';
            syncPerhubunganEmail(1);
        }
        if (k.pegawai_perhubungan_2) {
            ensureOptionExists('field_pegawai_perhubungan_2_id', k.pegawai_perhubungan_2.id, k.pegawai_perhubungan_2.name ?? '', k.pegawai_perhubungan_2.email ?? '');
            document.getElementById('field_pegawai_perhubungan_2_id').value = k.pegawai_perhubungan_2.id ?? '';
            syncPerhubunganEmail(2);
        }

        // Show catatan form
        document.getElementById('catatanForm').style.display = 'block';

        // Disable if selesai
        const isSelesai = k.status_kontrak === 'KONTRAK_SELESAI';
        document.querySelectorAll('#formKontrak input, #formKontrak select, #formKontrak textarea').forEach(el => {
            el.disabled = isSelesai;
        });
    }

    // ── Submit Kontrak ────────────────────────────────────────────────────────
    async function submitKontrak() {
        const form   = document.getElementById('formKontrak');
        clearKontrakErrors();
        const validation = ValidationService.validate(form, KONTRAK_SCHEMA, 'kontrakValidationSummary');
        if (!validation.valid) {
            return;
        }
        const data   = buildKontrakPayload(form);
        const isEdit = kontrakMode === 'kemaskini';
        const url    = isEdit ? `/kontrak/${currentKontrakId}` : '/kontrak';
        const method = isEdit ? 'PUT' : 'POST';
        const btn    = document.getElementById('btnSimpanKontrak');

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        try {
            const res  = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify(data),
            });
            const json = await res.json();

            if (json.success) {
                showToast(json.message ?? 'Kontrak berjaya disimpan.', 'success');
                closeKontrakModal();
                reloadKontrak();
            } else {
                showToast(json.message ?? 'Ralat berlaku.', 'error');
                if (json.errors) {
                    showValidationToast(json.errors, 'Sila lengkapkan medan kontrak yang wajib.');
                }
                showKontrakErrors(json.errors ?? {});
            }
        } catch (e) {
            showToast('Ralat sambungan.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Simpan';
        }
    }

    function buildKontrakPayload(form) {
        const fd = new FormData(form);
        const data = {};
        fd.forEach((v, k) => { if (!k.startsWith('_')) data[k] = v || null; });
        return data;
    }

    // ── Load Catatan ──────────────────────────────────────────────────────────
    async function loadCatatan(id) {
        const res  = await fetch(`/kontrak/${id}/catatan`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const list = json.data ?? [];
        const el   = document.getElementById('catatanList');

        if (list.length === 0) {
            el.innerHTML = '<div style="text-align:center;padding:1.5rem;color:#9ca3af;font-size:0.85rem;">Tiada catatan diterima.</div>';
            return;
        }

        el.innerHTML = `<table style="width:100%;border-collapse:collapse;font-size:0.83rem;">
            <thead><tr style="background:#f9fafb;">
                <th style="padding:0.5rem 0.75rem;text-align:left;font-size:0.7rem;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;">#</th>
                <th style="padding:0.5rem 0.75rem;text-align:left;font-size:0.7rem;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;">Status</th>
                <th style="padding:0.5rem 0.75rem;text-align:left;font-size:0.7rem;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;">Tahap</th>
                <th style="padding:0.5rem 0.75rem;text-align:left;font-size:0.7rem;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;">Catatan</th>
                <th style="padding:0.5rem 0.75rem;text-align:left;font-size:0.7rem;color:#6b7280;text-transform:uppercase;border-bottom:1px solid #e5e7eb;">Oleh</th>
            </tr></thead>
            <tbody>${list.map((c,i) => `
                <tr style="${i%2===0?'':'background:#f9fafb'}">
                    <td style="padding:0.6rem 0.75rem;color:#9ca3af;">${i+1}</td>
                    <td style="padding:0.6rem 0.75rem;font-size:0.8rem;">${c.status ?? '—'}</td>
                    <td style="padding:0.6rem 0.75rem;font-size:0.8rem;">${c.tahap ?? '—'}</td>
                    <td style="padding:0.6rem 0.75rem;">${c.catatan ?? '—'}</td>
                    <td style="padding:0.6rem 0.75rem;font-size:0.78rem;color:#6b7280;">${c.user?.name ?? '—'}</td>
                </tr>`).join('')}
            </tbody></table>`;
    }

    // ── Submit Catatan ────────────────────────────────────────────────────────
    async function submitCatatan() {
        if (!currentKontrakId) return;
        const data = {
            tahap:   document.getElementById('catatanTahap').value,
            status:  document.getElementById('catatanStatus').value,
            catatan: document.getElementById('catatanText').value,
        };

        const res  = await fetch(`/kontrak/${currentKontrakId}/catatan`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(data),
        });
        const json = await res.json();

        if (json.success) {
            showToast('Catatan berjaya ditambah.', 'success');
            document.getElementById('catatanTahap').value  = '';
            document.getElementById('catatanStatus').value = '';
            document.getElementById('catatanText').value   = '';
            loadCatatan(currentKontrakId);
        } else {
            showToast(json.message ?? 'Gagal menambah catatan.', 'error');
        }
    }

    function ensureOptionExists(selectId, value, label, email = '') {
        const select = document.getElementById(selectId);
        if (!select || !value) return;

        const exists = Array.from(select.options).some((opt) => String(opt.value) === String(value));
        if (exists) return;

        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label || `ID ${value}`;
        if (email) opt.dataset.email = email;
        select.appendChild(opt);
    }

    function syncPegawaiBertanggungjawabEmail() {
        const select = document.getElementById('field_pegawai_bertanggungjawab_id');
        const email = document.getElementById('pegawaiEmail');
        if (!select || !email) return;

        const selected = select.options[select.selectedIndex];
        email.value = selected?.dataset?.email || '';
        clearKontrakFieldError('pegawai_bertanggungjawab_id');
    }

    function syncPerhubunganEmail(slot) {
        const select = document.getElementById(`field_pegawai_perhubungan_${slot}_id`);
        const email = document.getElementById(`perhubungan${slot}Email`);
        if (!select || !email) return;

        const selected = select.options[select.selectedIndex];
        email.value = selected?.dataset?.email || '';
    }

    // ── Bahagian unit for kontrak ─────────────────────────────────────────────
    async function loadBahagianKontrak(jabatanId, selectedId = null) {
        const select = document.getElementById('field_bahagian_unit_id');
        select.innerHTML = '<option value="">Memuatkan...</option>';
        const selectedJabatanId = String(jabatanId || '').trim();

        const primaryUrl = selectedJabatanId
            ? `/pengguna/bahagian-unit?jabatan_id=${encodeURIComponent(selectedJabatanId)}`
            : '/pengguna/bahagian-unit';

        const res = await fetch(primaryUrl, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        let list = data.data ?? [];

        // Fallback to full list when selected jabatan has no mapped unit yet.
        if (selectedJabatanId && (!Array.isArray(list) || list.length === 0)) {
            const fallbackRes = await fetch('/pengguna/bahagian-unit', { headers: { 'Accept': 'application/json' } });
            const fallbackData = await fallbackRes.json();
            list = fallbackData.data ?? [];
        }

        select.innerHTML = '<option value="">— Pilih Unit —</option>';
        list.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id; opt.textContent = `${b.kod} — ${b.nama}`;
            if (selectedId && b.id == selectedId) opt.selected = true;
            select.appendChild(opt);
        });
    }

    // ── Tahun picker ──────────────────────────────────────────────────────────
    let pickerTarget = null;
    let yearStart = 2022;

    function initYearPicker() {
        const y1 = parseInt(document.getElementById('tahunMula').value || '', 10);
        const y2 = parseInt(document.getElementById('tahunTamat').value || '', 10);
        const base = Number.isFinite(y1) ? y1 : (Number.isFinite(y2) ? y2 : new Date().getFullYear());
        yearStart = Math.floor(base / 9) * 9;
        renderYearGrid();
    }

    function toggleYearPicker(target) {
        const pop = document.getElementById('yearPopover');
        const wrap = document.getElementById(target === 'mula' ? 'yearPickerMulaWrap' : 'yearPickerTamatWrap');
        const opening = pop.style.display !== 'block' || pickerTarget !== target;

        pickerTarget = target;
        if (!opening) {
            pop.style.display = 'none';
            return;
        }

        renderYearGrid();
        const r = wrap.getBoundingClientRect();
        const popWidth = 215;
        const popHeight = 270;
        const gap = 8;
        const viewportPadding = 12;
        const maxLeft = window.innerWidth - popWidth - viewportPadding;
        const maxTop = window.innerHeight - popHeight - viewportPadding;
        const left = Math.max(viewportPadding, Math.min(r.left, maxLeft));
        const below = r.bottom + gap;
        const above = r.top - gap - popHeight;
        const top = below <= maxTop ? below : Math.max(viewportPadding, above);

        pop.style.left = `${left}px`;
        pop.style.top = `${top}px`;
        pop.style.display = 'block';
    }

    function shiftYearRange(step) {
        yearStart += step;
        renderYearGrid();
    }

    function renderYearGrid() {
        const years = Array.from({ length: 9 }, (_, i) => yearStart + i);
        document.getElementById('yearRangeLabel').textContent = `${years[0]} - ${years[years.length - 1]}`;
        const active = pickerTarget === 'tamat' ? document.getElementById('tahunTamat').value : document.getElementById('tahunMula').value;
        document.getElementById('yearGrid').innerHTML = years.map(y =>
            `<button type="button" class="year-cell ${String(y) === active ? 'active' : ''}" onclick="selectYear(${y})">${y}</button>`
        ).join('');
    }

    function selectYear(y) {
        if (pickerTarget === 'tamat') {
            document.getElementById('tahunTamat').value = y;
            document.getElementById('yearBtnTamatText').textContent = y;
        } else {
            document.getElementById('tahunMula').value = y;
            document.getElementById('yearBtnMulaText').textContent = y;
        }
        document.getElementById('yearPopover').style.display = 'none';
        reloadKontrak(1);
    }

    function selectCurrentYear() {
        selectYear(new Date().getFullYear());
    }

    document.addEventListener('click', function (event) {
        const pop = document.getElementById('yearPopover');
        const mulaWrap = document.getElementById('yearPickerMulaWrap');
        const tamatWrap = document.getElementById('yearPickerTamatWrap');
        if (!pop || pop.style.display !== 'block') return;
        if (pop.contains(event.target) || mulaWrap.contains(event.target) || tamatWrap.contains(event.target)) return;
        pop.style.display = 'none';
    });

    // ── Table reload ──────────────────────────────────────────────────────────

    async function reloadKontrak(page = 1) {
        const perPageEl = document.getElementById('perPageKontrak');
        const perPage = perPageEl ? perPageEl.value : '10';
        const params = new URLSearchParams({
            tahun_mula: document.getElementById('tahunMula').value,
            tahun_tamat: document.getElementById('tahunTamat').value,
            page,
            per_page: perPage,
            _partial: '1',
        });
        const res = await fetch(`/kontrak/fetch?${params}`, {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
        });
        document.getElementById('kontrakTableWrap').innerHTML = await res.text();
    }

    function resetKontrakFilters() {
        document.getElementById('tahunMula').value = '';
        document.getElementById('tahunTamat').value = '';
        document.getElementById('yearBtnMulaText').textContent = 'Semua';
        document.getElementById('yearBtnTamatText').textContent = 'Semua';
        reloadKontrak(1);
    }

    function fmtDateShort(d) {
        if (!d) return '—';
        const iso = String(d).slice(0, 10);
        const m = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (m) return `${m[3]}-${m[2]}-${m[1]}`;
        return d;
    }

    function renderKontrakTable(data, wrap) {
        const rows = data.data ?? [];
        const meta = data.meta ?? {};
        const perPage = String(meta.per_page ?? document.getElementById('perPageKontrak')?.value ?? '10');
        const rowStart = (Number(meta.from ?? 0) > 0 ? Number(meta.from) : 1);
        const displayFrom = (Number(meta.total ?? 0) > 0 ? rowStart : 0);
        const displayTo = (Number(meta.total ?? 0) > 0 ? (Number(meta.to ?? 0) > 0 ? Number(meta.to) : (rowStart + Math.max(rows.length - 1, 0))) : 0);
        const statusClass = {
            'KONTRAK_SELESAI':'status-kontrak-selesai',
            'DALAM_PELAKSANAAN':'status-dalam-pelaksanaan',
            'MAKLUMAT_TIDAK_LENGKAP':'status-maklumat-tidak-lengkap',
            'DRAF_KONTRAK':'status-draf-kontrak',
            'DRAF':'status-draf',
            'EOT':'status-eot'
        };
        const statusLabel = {
            'KONTRAK_SELESAI':'Kontrak Selesai',
            'DALAM_PELAKSANAAN':'Dalam Pelaksanaan',
            'MAKLUMAT_TIDAK_LENGKAP':'Maklumat Tidak Lengkap',
            'DRAF_KONTRAK':'Draf Kontrak',
            'DRAF':'Draf',
            'EOT':'EOT'
        };

        const tableHtml = `
        <table>
            <thead><tr>
                <th>Bil</th>
                <th><span class="th-icon-wrap"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>Tajuk Kontrak</span></th>
                <th><span class="th-icon-wrap"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>No.Kontrak</span></th>
                <th><span class="th-icon-wrap"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1112 21a9 9 0 01-6.879-3.196z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Pemilik Projek</span></th>
                <th><span class="th-icon-wrap"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/></svg>Tempoh Kontrak</span></th>
                <th><span class="th-icon-wrap"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.59 13.41L11 3.83 4.41 10.41a2 2 0 000 2.83l6.34 6.34a2 2 0 002.83 0l7.01-7.01a2 2 0 000-2.83z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01"/></svg>Status</span></th>
                <th><span class="th-icon-wrap"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Status Draf<br>Kontrak</span></th>
                <th><span class="th-icon-wrap"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>Tarikh Tamat<br>Dimatikan Setem</span></th>
            </tr></thead>
            <tbody>${rows.length ? rows.map((k,i) => `
                <tr style="cursor:pointer;" onclick="openKontrakModal(${k.id})">
                    <td style="color:#9ca3af;font-size:0.8rem;">${rowStart + i}</td>
                    <td><a href="#" style="color:#2563eb;font-weight:500;text-decoration:none;" onclick="event.preventDefault();openKontrakModal(${k.id})">${k.tajuk_kontrak??'—'}</a></td>
                    <td class="kontrak-no-cell">${k.no_kontrak??'—'}</td>
                    <td style="font-size:0.82rem;">${k.jabatan?.nama??'—'}<br><small style="color:#9ca3af;">${k.bahagian_unit?.nama??''}</small></td>
                    <td style="font-size:0.8rem;white-space:nowrap;line-height:1.45;color:#475569;">${fmtDateShort(k.mula_tarikh)} - ${fmtDateShort(k.tamat_tarikh)}</td>
                    <td><span class="pill kontrak-status-pill ${statusClass[k.status_kontrak]??'pill-gray'}">${statusLabel[k.status_kontrak]??k.status_kontrak??'—'}</span></td>
                    <td style="font-size:0.8rem;color:#64748b;">${k.status_draf_kontrak ?? (k.status_draf_kompan ? 'Draf Disediakan' : '—')}</td>
                    <td style="font-size:0.8rem;">${fmtDateShort(k.tarikh_draf_hantar_sistem)}</td>
                </tr>`).join('') : '<tr><td colspan="8" style="text-align:center;padding:2rem;color:#9ca3af;">Tiada rekod kontrak dijumpai.</td></tr>'}
            </tbody>
        </table>
        <div class="pag-wrap">
            <div class="pag-info" style="display:flex;align-items:center;gap:0.45rem;">
                <span>Papar</span>
                <select class="per-page-select" id="perPageKontrak" onchange="reloadKontrak(1)">
                    <option value="10" ${perPage === '10' ? 'selected' : ''}>10</option>
                    <option value="25" ${perPage === '25' ? 'selected' : ''}>25</option>
                    <option value="50" ${perPage === '50' ? 'selected' : ''}>50</option>
                </select>
                <span>entri</span>
            </div>
            <div class="pag-info">Memaparkan <strong>${displayFrom}</strong> hingga <strong>${displayTo}</strong> daripada <strong>${meta.total ?? 0}</strong> entri</div>
            ${(meta.last_page ?? 1) > 1 ? `<div class="pag-btns">
                <button class="page-btn" onclick="reloadKontrak(1)" ${(meta.current_page ?? 1) <= 1 ? 'disabled' : ''}>«</button>
                <button class="page-btn" onclick="reloadKontrak(${(meta.current_page ?? 1) - 1})" ${(meta.current_page ?? 1) <= 1 ? 'disabled' : ''}>‹</button>
                ${Array.from({length: ((meta.last_page ?? 1))}, (_, i) => i + 1)
                    .filter(p => p >= Math.max(1, (meta.current_page ?? 1) - 2) && p <= Math.min((meta.last_page ?? 1), (meta.current_page ?? 1) + 2))
                    .map(p => `<button class="page-btn ${p === (meta.current_page ?? 1) ? 'active' : ''}" onclick="reloadKontrak(${p})">${p}</button>`).join('')}
                <button class="page-btn" onclick="reloadKontrak(${(meta.current_page ?? 1) + 1})" ${(meta.current_page ?? 1) >= (meta.last_page ?? 1) ? 'disabled' : ''}>›</button>
                <button class="page-btn" onclick="reloadKontrak(${meta.last_page ?? 1})" ${(meta.current_page ?? 1) >= (meta.last_page ?? 1) ? 'disabled' : ''}>»</button>
            </div>` : ''}
        </div>`;
        wrap.innerHTML = tableHtml;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function resetKontrakForm() {
        document.getElementById('formKontrak').reset();
        document.getElementById('kontrakId').value = '';
        document.getElementById('field_syarikat_id').value = '';
        document.getElementById('field_pegawai_bertanggungjawab_id').value = '';
        document.getElementById('pegawaiEmail').value = '';
        document.getElementById('field_pegawai_perhubungan_1_id').value = '';
        document.getElementById('perhubungan1Email').value = '';
        document.getElementById('field_pegawai_perhubungan_2_id').value = '';
        document.getElementById('perhubungan2Email').value = '';
        document.getElementById('catatanList').innerHTML = '<div style="text-align:center;padding:1.5rem;color:#9ca3af;font-size:0.85rem;">Tiada catatan diterima.</div>';
        document.querySelectorAll('#formKontrak input, #formKontrak select, #formKontrak textarea').forEach(el => el.disabled = false);
        clearKontrakErrors();

        // Preload full unit list when no jabatan is selected yet.
        loadBahagianKontrak('');
    }

    function clearKontrakErrors() {
        document.querySelectorAll('.invalid-note').forEach(el => el.textContent = '');
        document.querySelectorAll('#formKontrak .form-control').forEach(el => el.classList.remove('is-invalid'));
    }

    function getKontrakFieldElement(field) {
        return document.getElementById('field_' + field)
            || document.querySelector(`#formKontrak [name="${field}"]`);
    }

    function clearKontrakFieldError(field) {
        const note = document.getElementById('err_' + field);
        if (note) note.textContent = '';
        const el = getKontrakFieldElement(field);
        if (el) el.classList.remove('is-invalid');
    }

    function setKontrakFieldError(field, message) {
        const note = document.getElementById('err_' + field);
        if (note) note.textContent = message;
        const input = getKontrakFieldElement(field);
        if (input) input.classList.add('is-invalid');
    }

    function validateKontrakRequiredFields() {
        return ValidationService.validate(document.getElementById('formKontrak'), KONTRAK_SCHEMA, 'kontrakValidationSummary', {
            showToast: false,
            scrollToError: false,
        }).valid;
    }

    function showKontrakErrors(errors) {
        Object.entries(errors).forEach(([field, msgs]) => {
            const message = (Array.isArray(msgs) && msgs.length > 0) ? msgs[0] : 'Nilai tidak sah.';
            setKontrakFieldError(field, message);
        });
    }

    function setSelectValue(selector, value, isQuery = false) {
        const el = isQuery ? document.querySelector(selector) : document.getElementById(selector);
        if (el) el.value = value;
    }

    function escHtml(str) {
        return String(str).replace(/'/g, "\\'");
    }

    document.addEventListener('click', function (e) {
        const pop = document.getElementById('yearPopover');
        if (!pop || pop.style.display !== 'block') return;
        const withinPicker = e.target.closest('#yearPopover, #yearPickerMulaWrap, #yearPickerTamatWrap');
        if (!withinPicker) pop.style.display = 'none';
    });

    document.getElementById('formKontrak').addEventListener('input', function (e) {
        const field = e.target.name;
        if (field) clearKontrakFieldError(field);
    });

    document.getElementById('formKontrak').addEventListener('change', function (e) {
        const field = e.target.name;
        if (field) clearKontrakFieldError(field);
    });

    // Clear errors for dropdown fields
    document.getElementById('field_syarikat_id').addEventListener('change', function () {
        clearKontrakFieldError('syarikat_id');
    });
    document.getElementById('field_pegawai_bertanggungjawab_id').addEventListener('change', function () {
        syncPegawaiBertanggungjawabEmail();
    });
    document.getElementById('field_pegawai_perhubungan_1_id').addEventListener('change', function () {
        syncPerhubunganEmail(1);
    });
    document.getElementById('field_pegawai_perhubungan_2_id').addEventListener('change', function () {
        syncPerhubunganEmail(2);
    });

    ValidationService.bindRealtime(document.getElementById('formKontrak'), KONTRAK_SCHEMA);

    if (PEGAWAI_LOAD_ERROR) {
        console.error('[Kontrak] Pegawai lookup gagal dimuatkan:', {
            message: PEGAWAI_LOAD_MESSAGE,
            count: PEGAWAI_COUNT,
        });
        if (typeof showToast === 'function') {
            showToast('Gagal memuatkan senarai pegawai', 'error');
        }
    } else {
        console.debug('[Kontrak] Pegawai lookup dimuatkan:', { count: PEGAWAI_COUNT });
    }

    initYearPicker();
    syncPegawaiBertanggungjawabEmail();
    syncPerhubunganEmail(1);
    syncPerhubunganEmail(2);
</script>
@endpush
