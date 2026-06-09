<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Retur Tujuan Lain — {{ $bulanLabel }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    size: A5 portrait;
    margin: 0;
}

body {
    font-family: system-ui, -apple-system, sans-serif;
    background: #fff;
    font-size: 10px;
    color: #1e1c1a;
    line-height: 1.6;
}

/* ── TIAP RETUR = 1 PAGE ── */
.retur-page {
    width: 148mm;
    min-height: 210mm;
    padding: 10mm 11mm;
    display: flex;
    flex-direction: column;
    page-break-after: always;
}

/* ── HALAMAN SUMMARY ── */
.summary-page {
    width: 148mm;
    min-height: 210mm;
    padding: 10mm 11mm;
    display: flex;
    flex-direction: column;
    page-break-after: avoid;
}

/* ── HEADER ── */
.header {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 14px;
    align-items: start;
    margin-bottom: 6mm;
}

.brand-left {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.logo {
    width: 54px;
    height: 54px;
    object-fit: contain;
    flex-shrink: 0;
    margin-top: 1px;
}

.brand-name {
    font-size: 13px;
    font-weight: 700;
    color: #1e1c1a;
    line-height: 1.2;
    margin-bottom: 4px;
}

.brand-addr {
    font-size: 8px;
    color: #8a8278;
    line-height: 1.45;
}

.retur-box {
    border: 1px solid #ddd9d4;
    padding: 8px 12px;
    min-width: 130px;
    background: #faf9f7;
}

.retur-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: 8px;
    margin-bottom: 3px;
}
.retur-row:last-child { margin-bottom: 0; }
.retur-key { color: #8a8278; }
.retur-val {
    font-weight: 700;
    color: #1e1c1a;
    font-family: 'Courier New', monospace;
    font-size: 8px;
    text-align: right;
}

/* ── TITLE ── */
.title-area {
    text-align: center;
    padding: 5mm 0;
    border-top: 1px solid #ddd9d4;
    border-bottom: 1px solid #ddd9d4;
    margin-bottom: 5mm;
}
.title-ornament { font-size: 8px; color: #b5ada6; letter-spacing: 2px; margin-bottom: 3px; }
.title-text { font-size: 12px; font-weight: 700; color: #1e1c1a; }

/* ── OUTLET ── */
.outlet-row {
    display: flex;
    gap: 8px;
    align-items: baseline;
    margin-bottom: 5mm;
}
.outlet-label { font-size: 8px; color: #8a8278; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
.outlet-dots { flex: 1; border-bottom: 1px dotted #c5bfb6; margin-bottom: 2px; }
.outlet-val { font-size: 10px; font-weight: 700; color: #1e1c1a; }

/* ── TABLE ── */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4mm;
    font-size: 9px;
}
thead tr { background: #1e1c1a; }
th {
    padding: 6px 8px;
    text-align: left;
    font-weight: 700;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #faf9f7;
}
th:nth-child(2), td:nth-child(2) { text-align: center; }
th:nth-child(3), td:nth-child(3) { text-align: left; }
td { padding: 6px 8px; color: #3d3a37; }
tbody tr:nth-child(odd) td  { background: #faf9f7; }
tbody tr:nth-child(even) td { background: #ffffff; }

/* ── TOTAL ── */
.total-area {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 6mm;
    padding-top: 3mm;
    border-top: 1px solid #ddd9d4;
}
.total-block {
    display: flex;
    align-items: baseline;
    gap: 16px;
}
.total-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; color: #b5ada6; margin-bottom: 2px; }
.total-value { font-size: 18px; font-weight: 900; color: #1e1c1a; font-family: 'Courier New', monospace; letter-spacing: -0.5px; }

/* ── KETERANGAN ── */
.keterangan-box {
    background: #faf9f7;
    border: 1px solid #e8e4df;
    border-radius: 4px;
    padding: 7px 10px;
    margin-bottom: 5mm;
    font-size: 8.5px;
    color: #6b6460;
}
.keterangan-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #b5ada6; margin-bottom: 2px; }

/* ── SIGNATURES ── */
.signatures {
    display: flex;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 4mm;
}
.sig { text-align: center; width: 110px; }
.sig-line { border-bottom: 1px solid #c5bfb6; height: 36px; margin-bottom: 5px; }
.sig-label { font-size: 8px; letter-spacing: 0.5px; color: #8a8278; }

/* ── SUMMARY PAGE ── */
.summary-header { text-align: center; padding-bottom: 5mm; border-bottom: 1px solid #ddd9d4; margin-bottom: 6mm; }
.summary-title { font-size: 13px; font-weight: 700; color: #1e1c1a; margin-bottom: 3px; }
.summary-sub { font-size: 9px; color: #8a8278; }

.summary-reseller {
    background: #faf9f7;
    border: 1px solid #e8e4df;
    padding: 8px 12px;
    margin-bottom: 5mm;
    border-radius: 4px;
}
.summary-reseller-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.6px; color: #b5ada6; margin-bottom: 2px; }
.summary-reseller-val { font-size: 11px; font-weight: 700; color: #1e1c1a; }

/* summary table */
.summary-table { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 5mm; }
.summary-table thead tr { background: #1e1c1a; }
.summary-table th { padding: 6px 8px; text-align: left; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: #faf9f7; }
.summary-table th:nth-child(1) { text-align: left; }
.summary-table th:nth-child(2) { text-align: left; }
.summary-table th:nth-child(3) { text-align: center; }
.summary-table th:nth-child(4) { text-align: center; }
.summary-table td:nth-child(1) { text-align: left; }
.summary-table td:nth-child(2) { text-align: left; }
.summary-table td:nth-child(3) { text-align: center; }
.summary-table td:nth-child(4) { text-align: center; }
.summary-table td { padding: 6px 8px; color: #3d3a37; border-bottom: 1px solid #f0ede8; }
.summary-table tbody tr:nth-child(odd) td  { background: #faf9f7; }
.summary-table tbody tr:nth-child(even) td { background: #fff; }

/* grand total */
.grand-total-area { padding-top: 4mm; border-top: 2px solid #1e1c1a; margin-top: auto; }
.grand-total-block { display: flex; justify-content: space-between; align-items: baseline; padding-top: 3mm; }
.grand-total-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #b5ada6; }
.grand-total-value { font-size: 20px; font-weight: 900; color: #1e1c1a; font-family: 'Courier New', monospace; letter-spacing: -0.5px; }

/* badge */
.badge { display: inline-block; padding: 1.5px 7px; border-radius: 999px; font-size: 7.5px; font-weight: 700; letter-spacing: 0.3px; }
.badge-aktif { background: #dcfce7; color: #16a34a; }
.badge-batal { background: #fee2e2; color: #dc2626; }

@media print {
    body { background: white; }
    .no-print { display: none !important; }
}
</style>
</head>
<body>

{{-- TOMBOL PRINT --}}
<div class="no-print" style="padding:12px 20px; background:#f0ede8; display:flex; gap:10px; align-items:center;">
    <button onclick="window.print()"
        style="padding:8px 20px; background:#1e1c1a; color:white; border:none; border-radius:4px; font-size:12px; cursor:pointer; font-weight:600;">
        🖨️ Cetak Rekap Retur Tujuan Lain
    </button>
    <button onclick="window.close()"
        style="padding:8px 16px; background:transparent; color:#6b7280; border:1px solid #c5bfb6; border-radius:4px; font-size:12px; cursor:pointer;">
        Tutup
    </button>
    <span style="font-size:11px; color:#9a918a; margin-left:4px;">
        Tujuan Lain — {{ $bulanLabel }}
    </span>
</div>

{{-- TIAP RETUR = 1 HALAMAN --}}
@foreach ($returList as $retur)
<div class="retur-page">

    <div class="header">
        <div class="brand-left">
            <img src="{{ asset('images/logo.jpeg') }}" class="logo" alt="Logo">
            <div>
                <div class="brand-name">Bolu Legenda Malang</div>
                <div class="brand-addr">
                    Perumahan Oma Campus A8 No 4<br>
                    Landungsari Dau Malang<br>
                    WA 0822 44702525
                </div>
            </div>
        </div>
        <div class="retur-box">
            <div class="retur-row">
                <span class="retur-key">No. Retur</span>
                <span class="retur-val">{{ $retur->nomor_retur }}</span>
            </div>
            <div class="retur-row">
                <span class="retur-key">No. Invoice</span>
                <span class="retur-val">{{ $retur->distribusi->nomor_invoice }}</span>
            </div>
            <div class="retur-row">
                <span class="retur-key">Tanggal</span>
                <span class="retur-val">
                    {{ \Carbon\Carbon::parse($retur->tanggal)->locale('id')->translatedFormat('d M Y') }}
                </span>
            </div>
        </div>
    </div>

    <div class="title-area">
        <div class="title-ornament">— ✦ —</div>
        <div class="title-text">TANDA TERIMA RETUR BARANG</div>
    </div>

    <div class="outlet-row">
        <span class="outlet-label">Dari Customer</span>
        <span class="outlet-dots"></span>
        <span class="outlet-val">{{ $retur->distribusi->tujuan_lain }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Qty</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($retur->detail as $item)
            <tr>
                <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                <td>{{ $item->jumlah }}</td>
                <td>{{ match($item->alasan) {
                    'rusak'           => 'Barang Rusak',
                    'expired'         => 'Expired',
                    'salah_kirim'     => 'Salah Kirim',
                    'retur_pelanggan' => 'Retur Pelanggan',
                    'lainnya'         => $item->alasan_lain ?? 'Lainnya',
                    default           => $item->alasan ?? '-',
                } }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-area">
        <div class="total-block">
            <div class="total-label">Total Retur :</div>
            <div class="total-value">{{ $retur->detail->sum('jumlah') }} pcs</div>
        </div>
    </div>

    @if ($retur->keterangan)
    <div class="keterangan-box">
        <div class="keterangan-label">Keterangan</div>
        {{ $retur->keterangan }}
    </div>
    @endif

    <div class="signatures">
        <div class="sig">
            <div class="sig-label">Penerima,</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig">
            <div class="sig-label">Pengirim,</div>
            <div class="sig-line"></div>
        </div>
    </div>

</div>
@endforeach

{{-- HALAMAN TERAKHIR: SUMMARY --}}
<div class="summary-page">

    <div class="header">
        <div class="brand-left">
            <img src="{{ asset('images/logo.jpeg') }}" class="logo" alt="Logo">
            <div>
                <div class="brand-name">Bolu Legenda Malang</div>
                <div class="brand-addr">
                    Perumahan Oma Campus A8 No 4<br>
                    Landungsari Dau Malang<br>
                    WA 0822 44702525
                </div>
            </div>
        </div>
    </div>

    <div class="summary-header">
        <div class="summary-title">REKAP RETUR CUSTOMER</div>
        <div class="summary-sub">{{ $bulanLabel }}</div>
    </div>

    <div class="summary-reseller">
        <div class="summary-reseller-label">Kategori</div>
        <div class="summary-reseller-val">Semua Customer</div>
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>No. Retur</th>
                <th>Tujuan</th>
                <th>Tanggal</th>
                <th>Total Retur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($returList as $retur)
            <tr>
                <td style="font-family:'Courier New',monospace; font-size:8px;">
                    {{ $retur->nomor_retur }}
                </td>
                <td>{{ $retur->distribusi->tujuan_lain }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($retur->tanggal)->locale('id')->translatedFormat('d M Y') }}
                </td>
                <td style="text-align:center;">
                    {{ $retur->detail->sum('jumlah') }} pcs
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="grand-total-area">
        <div class="grand-total-block">
            <div class="grand-total-label">Total Retur Keseluruhan</div>
            <div class="grand-total-value">
                {{ $returList->sum(fn($r) => $r->detail->sum('jumlah')) }} pcs
            </div>
        </div>
    </div>

</div>

</body>
</html>
