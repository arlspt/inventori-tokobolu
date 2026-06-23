<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {{ $distribusi->nomor_invoice }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    size: A5 portrait;
    margin: 0;
}

body {
    font-family: 'Lato', system-ui, -apple-system, sans-serif;
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
    text-transform: uppercase;
}

/* ── INFO ROW (Tanggal + No Invoice) ── */
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 4mm;
    font-size: 9px;
}

.info-item {
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.info-label {
    color: #8a8278;
    white-space: nowrap;
}

.info-sep {
    color: #8a8278;
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
th:nth-child(3), td:nth-child(3) { text-align: right; }
th:nth-child(4), td:nth-child(4) { text-align: center; }
th:nth-child(5), td:nth-child(5) { text-align: right; }

td {
    padding: 6px 8px;
    color: #3d3a37;
    border-bottom: 1px solid #f0ede8;
}

tbody tr:last-child td { border-bottom: none; }

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
    letter-spacing: 0.5px;
    color: #1e1c1a;
}

.total-value {
    font-size: 16px;
    font-weight: 900;
    color: #1e1c1a;
    font-family: 'Courier New', monospace;
    letter-spacing: -0.5px;
}

/* ── SIGNATURES ── */
.signatures {
    display: flex;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 4mm;
}

.sig { text-align: center; width: 110px; }

.sig-label {
    font-size: 8px;
    color: #8a8278;
    margin-bottom: 2px;
}

.sig-line {
    border-bottom: 1px solid #c5bfb6;
    height: 36px;
    margin-bottom: 5px;
}

.sig-name {
    font-size: 8px;
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
        🖨️ Cetak Invoice
    </button>
    <button onclick="window.close()"
        style="padding:8px 16px; background:transparent; color:#6b7280; border:1px solid #c5bfb6; border-radius:4px; font-size:12px; cursor:pointer;">
        Tutup
    </button>
    <span style="font-size:11px; color:#9a918a; margin-left:4px;">{{ $distribusi->nomor_invoice }}</span>
</div>

<div class="page">

    {{-- HEADER --}}
    <div class="header">

        {{-- KIRI: LOGO + BRAND --}}
        <div class="brand-left">
            <img src="{{ asset('images/logo_login_hitam.png') }}" class="logo" alt="Logo">
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
    <span class="inv-key">
        {{ $distribusi->reseller ? 'Reseller :' : 'Customer :' }}
    </span>

    <span class="inv-val">
        {{ $distribusi->reseller?->nama_reseller ?? $distribusi->tujuan_lain }}
    </span>
</div>
            <div class="inv-row">
    <span class="inv-key">Alamat :</span>
    <span class="inv-val-reseller">
        @if($distribusi->reseller)
            {{ $distribusi->reseller->alamat }}
            @if($distribusi->reseller->kota)
                , {{ $distribusi->reseller->kota }}
            @endif
        @else
            -
        @endif
    </span>
</div>
        </div>

    </div>

    {{-- TITLE --}}
    <div class="title-area">
        <div class="title-ornament">— ✦ —</div>
        <div class="title-text">Tanda Terima Pengiriman Barang</div>
    </div>

    {{-- INFO ROW: TANGGAL (kiri) + NO INVOICE (kanan) --}}
    <div class="info-row">
        <div class="info-item">
            <span class="info-label">Tanggal :</span>
            <span class="info-val">
                {{ \Carbon\Carbon::parse($distribusi->tanggal)->locale('id')->translatedFormat('d M Y') }}
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">No. Invoice :</span>
            <span class="info-val">{{ $distribusi->nomor_invoice }}</span>
        </div>
    </div>

{{-- hitung retur per produk --}}
@php
    $returPerProduk = $distribusi->retur
        ->whereNull('deleted_at')
        ->flatMap(fn($r) => $r->detail)
        ->groupBy('produk_id')
        ->map(fn($items) => $items->sum('jumlah'));

    $totalDistribusi = $distribusi->detail->sum('subtotal');
    $totalRetur = 0;
@endphp

    {{-- TABEL PRODUK --}}
    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
            <th>Qty</th>
            <th>Harga Satuan</th>
            <th style="text-align:center;">Retur</th>
            <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($distribusi->detail as $item)
        @php
            $jumlahRetur   = $returPerProduk->get($item->produk_id, 0);
            $subtotalRetur = $jumlahRetur * $item->harga;
            $totalRetur   += $subtotalRetur;
            $subtotalBersih = $item->subtotal - $subtotalRetur;
        @endphp
        <tr>
            <td>{{ $item->produk->nama_produk ?? '-' }}</td>
            <td>{{ $item->jumlah }}</td>
            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
            <td style="text-align:center;">
                {{ $jumlahRetur > 0 ? '' . $jumlahRetur : '-' }}
            </td>
            <td>
                @if ($jumlahRetur > 0)
                    <span style="text-decoration:line-through; color:#b5ada6; font-size:8px;">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </span><br>
                    Rp {{ number_format($subtotalBersih, 0, ',', '.') }}
                @else
                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    </table>

    {{-- TOTAL: label kiri mentok, angka kanan mentok --}}
@php $totalAkhir = max(0, $totalDistribusi - $totalRetur); @endphp

@if ($totalRetur > 0)
<div style="display:flex; justify-content:space-between; font-size:9px; color:#dc2626; margin-bottom:2mm;">
    <span>Potongan Retur :</span>
    <span>- Rp {{ number_format($totalRetur, 0, ',', '.') }}</span>
</div>
@endif

<div class="total-area">
    <div class="total-label">Total :</div>
    <div class="total-value">
        Rp {{ number_format($totalAkhir, 0, ',', '.') }}
    </div>
</div>

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
