<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\AppSetting::get(\App\Models\AppSetting::APP_NAME, config('app.name')) }} — Dinas Sosial Kabupaten Buleleng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f0f4f8;
            color: #1a202c;
            min-height: 100vh;
        }

        /* HEADER */
        header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);
            color: white;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .header-top {
            background: rgba(0,0,0,0.15);
            padding: 8px 40px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-main {
            padding: 24px 40px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .logo-circle {
            width: 72px;
            height: 72px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: #1e3a5f;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .header-text h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header-text h2 {
            font-size: 14px;
            font-weight: 400;
            opacity: 0.85;
            margin-top: 4px;
        }

        .header-text p {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 2px;
        }

        /* NAV */
        nav {
            background: rgba(0,0,0,0.2);
            padding: 0 40px;
            display: flex;
            gap: 4px;
        }

        nav a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 12px 16px;
            font-size: 14px;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        nav a:hover, nav a.active {
            color: white;
            border-bottom-color: #60a5fa;
            background: rgba(255,255,255,0.08);
        }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 60%, #3b82f6 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }

        .hero h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .hero p {
            font-size: 17px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 32px;
            line-height: 1.6;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #1e3a5f;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            transition: all 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }

        /* STATS BAR */
        .stats-bar {
            background: white;
            padding: 24px 40px;
            display: flex;
            justify-content: center;
            gap: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-item {
            text-align: center;
            padding: 0 40px;
            border-right: 1px solid #e2e8f0;
        }

        .stat-item:last-child { border-right: none; }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a5f;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        /* MAIN CONTENT */
        main {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 40px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 8px;
        }

        .section-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 24px;
        }

        /* INFO CARDS */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 48px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .card-icon.blue { background: #eff6ff; }
        .card-icon.green { background: #f0fdf4; }
        .card-icon.orange { background: #fff7ed; }
        .card-icon.purple { background: #faf5ff; }
        .card-icon.red { background: #fff1f2; }
        .card-icon.teal { background: #f0fdfa; }

        .card h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 8px;
        }

        .card p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        /* ALUR */
        .alur-section {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            margin-bottom: 48px;
        }

        .alur-steps {
            display: flex;
            gap: 0;
            margin-top: 24px;
            overflow-x: auto;
        }

        .alur-step {
            flex: 1;
            text-align: center;
            position: relative;
            min-width: 120px;
        }

        .alur-step::after {
            content: '→';
            position: absolute;
            right: -12px;
            top: 20px;
            font-size: 20px;
            color: #94a3b8;
        }

        .alur-step:last-child::after { display: none; }

        .step-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto 12px;
        }

        .step-label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .step-desc {
            font-size: 11px;
            color: #94a3b8;
        }

        .step-1 .step-circle { background: #eff6ff; }
        .step-2 .step-circle { background: #f0fdf4; }
        .step-3 .step-circle { background: #fff7ed; }
        .step-4 .step-circle { background: #faf5ff; }
        .step-5 .step-circle { background: #f0fdfa; }

        /* KATEGORI PMKS */
        .kategori-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 48px;
        }

        .kategori-item {
            background: white;
            border-radius: 8px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3b82f6;
            font-size: 12px;
        }

        .kategori-item .kode {
            font-weight: 700;
            color: #1e3a5f;
            font-size: 11px;
        }

        .kategori-item .nama {
            color: #374151;
            margin-top: 2px;
        }

        .kategori-item .aturan {
            margin-top: 6px;
            font-size: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            font-weight: 600;
        }

        /* FOOTER */
        footer {
            background: #1e3a5f;
            color: rgba(255,255,255,0.7);
            text-align: center;
            padding: 32px 40px;
            font-size: 13px;
            line-height: 1.8;
            margin-top: 60px;
        }

        footer strong {
            color: white;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header-main { padding: 16px 20px; }
            nav { padding: 0 20px; overflow-x: auto; }
            .hero { padding: 40px 20px; }
            .hero h2 { font-size: 22px; }
            .stats-bar { flex-wrap: wrap; padding: 16px; gap: 16px; }
            .stat-item { border-right: none; padding: 8px 20px; }
            main { padding: 0 20px; }
            .cards-grid { grid-template-columns: 1fr; }
            .kategori-grid { grid-template-columns: repeat(2, 1fr); }
            .alur-steps { flex-direction: column; gap: 16px; }
            .alur-step::after { display: none; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="header-top">
        🏛️ Pemerintah Kabupaten Buleleng — Dinas Sosial
    </div>
    <div class="header-main">
        @php $appLogo = \App\Models\AppSetting::get(\App\Models\AppSetting::APP_LOGO); @endphp
        <div class="logo-circle" style="{{ $appLogo ? 'padding:0;overflow:hidden;' : '' }}">
            @if($appLogo)
                <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
            @else
                DS
            @endif
        </div>
        <div class="header-text">
            <h1>{{ \App\Models\AppSetting::get(\App\Models\AppSetting::APP_NAME, config('app.name')) }}</h1>
            <h2>Sistem Pendataan PMKS & PSKS</h2>
            <p>Pusat Kesejahteraan Sosial Generasi Cerdas Terpadu — Kabupaten Buleleng, Bali</p>
        </div>
    </div>
    <nav>
        <a href="/" class="active">Beranda</a>
        <a href="#tentang">Tentang</a>
        <a href="#pmks">Kategori PMKS</a>
        <a href="#psks">Kategori PSKS</a>
        <a href="#alur">Alur Pengajuan</a>
        <a href="/admin" style="margin-left:auto; background:rgba(255,255,255,0.15); border-radius:6px 6px 0 0;">
            🔐 Login Sistem
        </a>
    </nav>
</header>

<!-- HERO -->
<div class="hero">
    <h2>Sistem Pendataan Kesejahteraan Sosial</h2>
    <p>Platform terintegrasi untuk pendataan Penyandang Masalah Kesejahteraan Sosial (PMKS) dan Potensi Sumber Kesejahteraan Sosial (PSKS) di seluruh wilayah Kabupaten Buleleng.</p>
    <a href="/admin" class="btn-login">
        🔐 Masuk ke Sistem
    </a>
</div>

<!-- STATS -->
<div class="stats-bar">
    <div class="stat-item">
        <div class="stat-number">{{ $totalKecamatan }}</div>
        <div class="stat-label">Kecamatan</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">{{ $totalDesa }}</div>
        <div class="stat-label">Desa / Kelurahan</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">{{ $pmksCategories->count() }}</div>
        <div class="stat-label">Kategori PMKS</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">{{ $psksCategories->count() }}</div>
        <div class="stat-label">Kategori PSKS</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">4</div>
        <div class="stat-label">Level Pengguna</div>
    </div>
</div>

<!-- MAIN -->
<main>

    <!-- TENTANG -->
    <div id="tentang" style="padding-top:20px; margin-bottom:48px;">
        <div class="section-title">Tentang Sistem</div>
        <div class="section-subtitle">Fitur utama yang tersedia dalam sistem ini</div>
        <div class="cards-grid">
            <div class="card">
                <div class="card-icon blue">📊</div>
                <h3>Pendataan PMKS</h3>
                <p>Pendataan 24 kategori Penyandang Masalah Kesejahteraan Sosial dengan validasi usia dan gender sesuai standar Kemensos RI.</p>
            </div>
            <div class="card">
                <div class="card-icon green">🤝</div>
                <h3>Pendataan PSKS</h3>
                <p>Pendataan 12 kategori Potensi Sumber Kesejahteraan Sosial mencakup individu (PSM, TKSK, Tagana) dan lembaga (Karang Taruna, PKK, LKS).</p>
            </div>
            <div class="card">
                <div class="card-icon orange">📋</div>
                <h3>Alur Pengajuan</h3>
                <p>Pengajuan data tahunan per desa dengan alur: draft → verifikasi → persetujuan Admin Dinsos. Data terkunci setelah disetujui.</p>
            </div>
            <div class="card">
                <div class="card-icon purple">🔐</div>
                <h3>Keamanan Berlapis</h3>
                <p>Sistem role-based access control dengan 4 level pengguna. Operator Desa hanya bisa akses data desanya sendiri.</p>
            </div>
            <div class="card">
                <div class="card-icon teal">📁</div>
                <h3>Export Excel</h3>
                <p>Data PMKS dan PSKS dapat diekspor ke format Excel untuk keperluan pelaporan ke Kemensos dan pimpinan Dinsos.</p>
            </div>
            <div class="card">
                <div class="card-icon red">📝</div>
                <h3>Audit Log</h3>
                <p>Setiap perubahan data tercatat otomatis dalam sistem audit log yang hanya dapat diakses oleh Admin Dinsos.</p>
            </div>
        </div>
    </div>

    <!-- ALUR PENGAJUAN -->
    <div id="alur" class="alur-section">
        <div class="section-title">Alur Pengajuan Data</div>
        <div class="section-subtitle">Proses pengajuan data PMKS & PSKS per tahun per desa</div>
        <div class="alur-steps">
            <div class="alur-step step-1">
                <div class="step-circle">📝</div>
                <div class="step-label">Input Data</div>
                <div class="step-desc">Operator Desa input KK, penduduk & lembaga</div>
            </div>
            <div class="alur-step step-2">
                <div class="step-circle">📦</div>
                <div class="step-label">Buat Batch</div>
                <div class="step-desc">Buat pengajuan tahunan & isi data PMKS/PSKS</div>
            </div>
            <div class="alur-step step-3">
                <div class="step-circle">🚀</div>
                <div class="step-label">Ajukan</div>
                <div class="step-desc">Submit ke Verifikator Dinsos</div>
            </div>
            <div class="alur-step step-4">
                <div class="step-circle">✅</div>
                <div class="step-label">Verifikasi</div>
                <div class="step-desc">Verifikator review & teruskan ke Admin</div>
            </div>
            <div class="alur-step step-5">
                <div class="step-circle">🏆</div>
                <div class="step-label">Disetujui</div>
                <div class="step-desc">Admin Dinsos approve & data terkunci</div>
            </div>
        </div>
    </div>

    <!-- KATEGORI PMKS -->
    <div id="pmks" style="margin-bottom:48px;">
        <div class="section-title">Kategori PMKS</div>
        <div class="section-subtitle">{{ $pmksCategories->count() }} kategori Penyandang Masalah Kesejahteraan Sosial sesuai standar Kemensos RI</div>
        <div class="kategori-grid">
            @forelse($pmksCategories as $kategori)
            <div class="kategori-item">
                <div class="kode">{{ $kategori->code }}</div>
                <div class="nama">{{ $kategori->name }}</div>
                @if($kategori->hasAgeRestriction())
                    <span class="aturan">{{ $kategori->ageLabel() }}</span>
                @endif
                @if($kategori->hasGenderRestriction())
                    <span class="aturan" style="background:#fdf4ff;color:#7e22ce;">{{ $kategori->genderLabel() }}</span>
                @endif
            </div>
            @empty
            <div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:24px;">
                Belum ada kategori PMKS yang aktif.
            </div>
            @endforelse
        </div>
    </div>

    <!-- KATEGORI PSKS -->
    <div id="psks" style="margin-bottom:48px;">
        <div class="section-title">Kategori PSKS</div>
        <div class="section-subtitle">{{ $psksCategories->count() }} kategori Potensi Sumber Kesejahteraan Sosial</div>
        <div class="kategori-grid">
            @forelse($psksCategories as $kategori)
            <div class="kategori-item" style="border-left-color: {{ $kategori->subject_type === 'person' ? '#10b981' : '#8b5cf6' }}">
                <div class="kode">{{ $kategori->code }}</div>
                <div class="nama">{{ $kategori->name }}</div>
                <span class="aturan" style="background: {{ $kategori->subject_type === 'person' ? '#f0fdf4' : '#faf5ff' }}; color: {{ $kategori->subject_type === 'person' ? '#065f46' : '#5b21b6' }}">
                    {{ $kategori->subject_type === 'person' ? 'Individu' : 'Lembaga' }}
                </span>
            </div>
            @empty
            <div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:24px;">
                Belum ada kategori PSKS yang aktif.
            </div>
            @endforelse
        </div>
    </div>

<!-- FOOTER -->
<footer>
    <strong>Dinas Sosial Kabupaten Buleleng</strong><br>
    Sistem Pendataan PMKS & PSKS — {{ \App\Models\AppSetting::get(\App\Models\AppSetting::APP_NAME, config('app.name')) }}<br>
    Kabupaten Buleleng, Provinsi Bali<br><br>
    <small>© {{ date('Y') }} Dinas Sosial Kabupaten Buleleng. Hak cipta dilindungi undang-undang.</small>
</footer>

</body>
</html>
