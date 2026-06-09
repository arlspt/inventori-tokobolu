<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Bulanan — {{ $reseller->nama_reseller }} — {{ $bulanLabel }}</title>
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

/* ── TIAP INVOICE = 1 PAGE ── */
.invoice-page {
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
.inv-key { color: #8a8278; white-space: nowrap; }
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
    line-height: 1.4;
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
.info-item { display: flex; align-items: baseline; gap: 6px; }
.info-label { color: #8a8278; white-space: nowrap; }
.info-sep   { color: #8a8278; }
.info-val   { font-weight: 700; font-size: 10px; color: #1e1c1a; }

/* ── OUTLET ROW ── */
.outlet-row {
    display: flex;
    gap: 8px;
    align-items: baseline;
    margin-bottom: 5mm;
}
.outlet-label {
    font-size: 8px;
    color: #8a8278;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.outlet-dots {
    flex: 1;
    border-bottom: 1px dotted #c5bfb6;
    margin-bottom: 2px;
}
.outlet-val {
    font-size: 10px;
    font-weight: 700;
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
th:nth-child(3), td:nth-child(3),
th:nth-child(4), td:nth-child(4) { text-align: right; }
td {
    padding: 6px 8px;
    color: #3d3a37;
    border-bottom: 1px solid #f0ede8;
}
tbody tr:last-child td { border-bottom: none; }

/* ── TOTAL per invoice ── */
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
.sig-line {
    border-bottom: 1px solid #c5bfb6;
    height: 36px;
    margin-bottom: 5px;
}
.sig-label {
    font-size: 8px;
    color: #8a8278;
    margin-bottom: 2px;
}

/* ── SUMMARY PAGE ── */
.summary-header {
    text-align: center;
    padding-bottom: 5mm;
    border-bottom: 1px solid #ddd9d4;
    margin-bottom: 6mm;
}
.summary-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e1c1a;
    margin-bottom: 3px;
}
.summary-sub {
    font-size: 9px;
    color: #8a8278;
}

.summary-reseller {
    background: #faf9f7;
    border: 1px solid #e8e4df;
    padding: 8px 12px;
    margin-bottom: 5mm;
    border-radius: 4px;
}
.summary-reseller-label {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #b5ada6;
    margin-bottom: 2px;
}
.summary-reseller-val {
    font-size: 11px;
    font-weight: 700;
    color: #1e1c1a;
}

.summary-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
    margin-bottom: 5mm;
}
.summary-table thead tr { background: #1e1c1a; }
.summary-table th {
    padding: 6px 8px;
    text-align: left;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #faf9f7;
}
/* kolom per posisi summary table */
.summary-table th:nth-child(1) { text-align: left; }
.summary-table th:nth-child(2) { text-align: center; }
.summary-table th:nth-child(3) { text-align: right; }
.summary-table th:nth-child(4) { text-align: center; }

.summary-table td:nth-child(1) { text-align: left; }
.summary-table td:nth-child(2) { text-align: center; }
.summary-table td:nth-child(3) { text-align: right; }
.summary-table td:nth-child(4) { text-align: center; }
.summary-table td {
    padding: 6px 8px;
    color: #3d3a37;
    border-bottom: 1px solid #f0ede8;
}
.summary-table tbody tr:nth-child(odd) td  { background: #faf9f7; }
.summary-table tbody tr:nth-child(even) td { background: #fff; }

.grand-total-area {
    padding-top: 4mm;
    border-top: 2px solid #1e1c1a;
    margin-top: auto;
}

/* ── RINGKASAN PEMBAYARAN ── */
.payment-summary {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 4mm;
}
.payment-summary-box {
    min-width: 200px;
}
.payment-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid #f0ede8;
    font-size: 9px;
}
.payment-row:last-child { border-bottom: none; }
.payment-row-label { color: #8a8278; }
.payment-row-value { font-weight: 700; font-family: 'Courier New', monospace; }
.payment-row-value.lunas { color: #16a34a; }
.payment-row-value.belum { color: #d97706; }

.grand-total-block {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding-top: 3mm;
}
.grand-total-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #b5ada6;
}
.grand-total-value {
    font-size: 20px;
    font-weight: 900;
    color: #1e1c1a;
    font-family: 'Courier New', monospace;
    letter-spacing: -0.5px;
}

/* ── STATUS BADGE ── */
.badge {
    display: inline-block;
    padding: 1.5px 7px;
    border-radius: 999px;
    font-size: 7.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.badge-lunas {
    background: #dcfce7;
    color: #16a34a;
}
.badge-belum {
    background: #fef3c7;
    color: #d97706;
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
        🖨️ Cetak Rekap Bulanan
    </button>
    <button onclick="window.close()"
        style="padding:8px 16px; background:transparent; color:#6b7280; border:1px solid #c5bfb6; border-radius:4px; font-size:12px; cursor:pointer;">
        Tutup
    </button>
    <span style="font-size:11px; color:#9a918a; margin-left:4px;">
        {{ $reseller->nama_reseller }} — {{ $bulanLabel }}
    </span>
</div>

{{-- TIAP INVOICE = 1 HALAMAN --}}
@foreach ($distribusiList as $distribusi)
<div class="invoice-page">

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
        <div class="inv-box">
            <div class="inv-row">
                <span class="inv-key">Reseller :</span>
                <span class="inv-val">{{ $reseller->nama_reseller }}</span>
            </div>
            <div class="inv-row">
                <span class="inv-key">Alamat :</span>
                <span class="inv-val-reseller">
                    @if ($reseller->alamat)
                        {{ $reseller->alamat }}
                        @if ($reseller->kota), {{ $reseller->kota }}@endif
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="title-area">
        <div class="title-ornament">— ✦ —</div>
        <div class="title-text">Tanda Terima Pengiriman Barang</div>
    </div>

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

    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($distribusi->detail as $item)
            <tr>
                <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                <td>{{ $item->jumlah }}</td>
                <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-area">
        <div class="total-label">Total :</div>
        <div class="total-value">
            Rp {{ number_format($distribusi->detail->sum('subtotal'), 0, ',', '.') }}
        </div>
    </div>

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

{{-- HALAMAN TERAKHIR: SUMMARY GRAND TOTAL --}}
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
        <div class="summary-title">Rekap Invoice Bulanan</div>
        <div class="summary-sub">{{ $bulanLabel }}</div>
    </div>

    <div class="summary-reseller">
        <div class="summary-reseller-label">Nama Reseller / Outlet</div>
        <div class="summary-reseller-val">{{ $reseller->nama_reseller }}</div>
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>No. Invoice</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($distribusiList as $distribusi)
            <tr>
                <td style="font-family:'Courier New',monospace; font-size:8.5px;">
                    {{ $distribusi->nomor_invoice }}
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($distribusi->tanggal)->locale('id')->translatedFormat('d M Y') }}
                </td>
                <td>
                    Rp {{ number_format($distribusi->detail->sum('subtotal'), 0, ',', '.') }}
                </td>
                <td>
                    @if ($distribusi->status_pembayaran === 'lunas')
                        <span class="badge badge-lunas">✓ Lunas</span>
                    @else
                        <span class="badge badge-belum">⏳ Belum Bayar</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="grand-total-area">

        {{-- RINGKASAN LUNAS / BELUM BAYAR --}}
        @php
            $totalLunas  = $distribusiList->where('status_pembayaran', 'lunas')->sum(fn($d) => $d->detail->sum('subtotal'));
            $totalBelum  = $distribusiList->where('status_pembayaran', '!=', 'lunas')->sum(fn($d) => $d->detail->sum('subtotal'));
            $grandTotal  = $totalLunas + $totalBelum;
        @endphp

        <div style="width:100%;">
            <div class="payment-summary">
                <div class="payment-summary-box">
                    <div class="payment-row">
                        <span class="payment-row-label">Total Lunas</span>
                        <span class="payment-row-value lunas">
                            Rp {{ number_format($totalLunas, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="payment-row">
                        <span class="payment-row-label">Total Belum Bayar</span>
                        <span class="payment-row-value belum">
                            Rp {{ number_format($totalBelum, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grand-total-block">
                <div class="grand-total-label">Grand Total</div>
                <div class="grand-total-value">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
