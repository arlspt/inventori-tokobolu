<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Retur {{ $retur->nomor_retur }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    size: A5 portrait;
    margin: 0;
}

body {
    font-family: system-ui, -apple-system, sans-serif;
    background: #fff;
    width: 148mm;
    min-height: 210mm;
    font-size: 10px;
    color: #1e1c1a;
    line-height: 1.6;
}

.page {
    width: 148mm;
    min-height: 210mm;
    padding: 10mm 11mm;
    display: flex;
    flex-direction: column;
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
    gap: 10px;
}

.logo {
    width: 54px;
    height: 54px;
    object-fit: contain;
    flex-shrink: 0;
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
    line-height: 1.5;
}

/* ── KOTAK KANAN ATAS ── */
.inv-box {
    border: 1px solid #ddd9d4;
    padding: 8px 12px;
    min-width: 148px;
    max-width: 148px;
    background: #faf9f7;
}

.inv-row {
    display: grid;
    grid-template-columns: 40px 1fr;
    font-size: 8px;
    margin-bottom: 4px;
    align-items: start;
}
.inv-row:last-child { margin-bottom: 0; }

.inv-key {
    color: #8a8278;
    white-space: nowrap;
}

.inv-val {
    font-weight: 500;
    color: #1e1c1a;
    font-size: 8px;
    word-break: break-word;
}

.inv-val-reseller {
    font-weight: 500;
    color: #1e1c1a;
    font-size: 8px;
    word-break: break-word;
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

/* ── TITLE AREA ── */
.title-area {
    text-align: center;
    padding: 5mm 0;
    border-top: 1px solid #ddd9d4;
    border-bottom: 1px solid #ddd9d4;
    margin-bottom: 5mm;
}

.title-ornament {
    font-size: 8px;
    color: #b5ada6;
    letter-spacing: 2px;
    margin-bottom: 3px;
}

.title-text {
    font-size: 12px;
    font-weight: 700;
    color: #1e1c1a;
    letter-spacing: 0.3px;
}

/* ── INFO ROW (Tanggal + No Invoice) ── */
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 4mm;
    font-size: 9px;
}

.info-item-column {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-item {
    display: flex;
    align-items: baseline;
}

.info-label {
    width: 75px; /* samakan panjang label */
    color: #8a8278;
    white-space: nowrap;
}

.info-val {
    font-weight: 700;
    font-size: 10px;
    color: #1e1c1a;
}

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

td {
    padding: 6px 8px;
    color: #3d3a37;
}

tbody tr:nth-child(odd) td  { background: #faf9f7; }
tbody tr:nth-child(even) td { background: #ffffff; }

/* ── TOTAL ── */
.total-area {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 6mm;
    padding-top: 3mm;
    border-top: 1.5px solid #1e1c1a;
}

.total-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: #1e1c1a;
}

.total-value {
    font-size: 16px;
    font-weight: 900;
    color: #1e1c1a;
    font-family: 'Courier New', monospace;
    letter-spacing: -0.5px;
}

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

.keterangan-label {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #b5ada6;
    margin-bottom: 2px;
}

/* ── SIGNATURES ── */
.signatures {
    display: flex;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 4mm;
}

.sig { text-align: center; width: 110px; }

.sig-line {
    border-bottom: 1px solid #c5bfb6;
    height: 36px;
    margin-bottom: 5px;
}

.sig-label {
    font-size: 8px;
    letter-spacing: 0.5px;
    color: #8a8278;
}

/* ── PRINT ── */
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
        🖨️ Cetak Retur
    </button>
    <button onclick="window.close()"
        style="padding:8px 16px; background:transparent; color:#6b7280; border:1px solid #c5bfb6; border-radius:4px; font-size:12px; cursor:pointer;">
        Tutup
    </button>
    <span style="font-size:11px; color:#9a918a; margin-left:4px;">{{ $retur->nomor_retur }}</span>
</div>

<div class="page">

    {{-- HEADER --}}
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
        {{-- KANAN: NO INVOICE + NAMA & ALAMAT RESELLER --}}
        <div class="inv-box">
            <div class="inv-row">
                <span class="inv-key">Reseller :</span>
                <span class="inv-val">{{ $retur->distribusi->reseller
                ? $retur->distribusi->reseller->nama_reseller
                : $retur->distribusi->tujuan_lain }}</span>
            </div>
            <div class="inv-row">
                <span class="inv-key">Alamat :</span>
                <span class="inv-val-reseller">
                    @if ($retur->distribusi->reseller)
                        @if ($retur->distribusi->reseller->alamat)
                            {{ $retur->distribusi->reseller->alamat}}
                            @if ($retur->distribusi->reseller->kota)
                            , {{ $retur->distribusi->reseller->kota }}
                            @endif
                        @endif
                    @else
                        {{ $retur->distribusi->tujuan_lain }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- TITLE --}}
    <div class="title-area">
        <div class="title-ornament">— ✦ —</div>
        <div class="title-text">TANDA TERIMA RETUR BARANG</div>
    </div>

    {{-- OUTLET --}}
    <div class="info-row">
            <div class="info-item">
        <span class="info-label">Tanggal Retur:</span>
            <span class="info-val">
                {{ \Carbon\Carbon::parse($retur->distribusi->tanggal)->locale('id')->translatedFormat('d M Y') }}
            </span>
            </div>
            <div class="info-item-column">

    <div class="info-item">
        <span class="info-label">No. Invoice :</span>
        <span class="info-val">{{ $retur->distribusi->nomor_invoice }}</span>
    </div>

    <div class="info-item">
        <span class="info-label">No. Retur :</span>
        <span class="info-val">{{ $retur->nomor_retur }}</span>
    </div>

</div>
    </div>

    {{-- TABEL DETAIL RETUR --}}
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

    {{-- TOTAL RETUR --}}
    <div class="total-area">
    <div class="total-label">Total Retur :</div>
    <div class="total-value">{{ $retur->detail->sum('jumlah') }} pcs</div>
</div>

    {{-- KETERANGAN (kalau ada) --}}
    @if ($retur->keterangan)
    <div class="keterangan-box">
        <div class="keterangan-label">Keterangan</div>
        {{ $retur->keterangan }}
    </div>
    @endif

    {{-- TANDA TANGAN --}}
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
</body>
</html>
