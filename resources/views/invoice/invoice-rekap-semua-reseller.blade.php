<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Semua Reseller - {{ $bulanLabel }}</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

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

.page {
    width: 148mm;
    min-height: 210mm;
    padding: 10mm 11mm;
    display: flex;
    flex-direction: column;
}

/* ─────────────────────────
   HEADER
───────────────────────── */

.header {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    align-items: start;
    margin-bottom: 6mm;
}

.brand {
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

.brand-address {
    font-size: 8px;
    color: #8a8278;
    line-height: 1.5;
}

/* ─────────────────────────
   TITLE
───────────────────────── */

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

.title {
    font-size: 12px;
    font-weight: 700;
    color: #1e1c1a;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

.subtitle {
    margin-top: 3px;
    font-size: 9px;
    color: #8a8278;
}

/* ─────────────────────────
   SUMMARY CARD
───────────────────────── */

.summary-card {
    background: #faf9f7;
    border: 1px solid #e8e4df;
    padding: 8px 12px;
    margin-bottom: 5mm;
    border-radius: 4px;
}

.summary-label {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #b5ada6;
    margin-bottom: 2px;
}

.summary-value {
    font-size: 11px;
    font-weight: 700;
    color: #1e1c1a;
}

/* ─────────────────────────
   TABLE
───────────────────────── */

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5mm;
    font-size: 9px;
}

thead tr {
    background: #1e1c1a;
}

th {
    padding: 6px 8px;
    text-align: left;
    font-weight: 700;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #faf9f7;
}

th:nth-child(2),
td:nth-child(2) {
    text-align: center;
}

th:nth-child(3),
td:nth-child(3),
th:nth-child(4),
td:nth-child(4) {
    text-align: right;
}

td {
    padding: 6px 8px;
    color: #3d3a37;
    border-bottom: 1px solid #f0ede8;
}

tbody tr:nth-child(odd) td {
    background: #faf9f7;
}

tbody tr:nth-child(even) td {
    background: #fff;
}

/* ─────────────────────────
   GRAND TOTAL AREA
───────────────────────── */

.grand-total-area {
    margin-top: auto;
    padding-top: 4mm;
    border-top: 2px solid #1e1c1a;
}

.payment-summary {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 4mm;
}

.payment-summary-box {
    min-width: 220px;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid #f0ede8;
    font-size: 10px;
}

.payment-row:last-child {
    border-bottom: none;
}

.payment-row-label {
    color: #8a8278;
}

.payment-row-value {
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

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

/* ─────────────────────────
   BUTTONS
───────────────────────── */

.no-print {
    padding: 12px 20px;
    background: #f0ede8;
    display: flex;
    gap: 10px;
    align-items: center;
}

.print-btn {
    padding: 8px 20px;
    background: #1e1c1a;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    font-weight: 600;
}

.close-btn {
    padding: 8px 16px;
    background: transparent;
    color: #6b7280;
    border: 1px solid #c5bfb6;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

/* ─────────────────────────
   PRINT
───────────────────────── */

@media print {

    body {
        background: white;
    }

    .no-print {
        display: none !important;
    }

    .page {
        width: auto;
        min-height: auto;
        padding: 10mm 11mm;
    }
}

</style>
</head>

<body>

<div class="no-print">

    <button
        onclick="window.print()"
        class="print-btn">
        🖨️ Cetak Rekap
    </button>

    <button
        onclick="window.close()"
        class="close-btn">
        Tutup
    </button>

</div>

<div class="page">

    <div class="header">

        <div class="brand">

            <img
                src="{{ asset('images/logo_login_hitam.png') }}"
                class="logo"
                alt="Logo">

            <div>

                <div class="brand-name">
                    Bolu Legenda Malang
                </div>

                <div class="brand-address">
                    Perumahan Oma Campus A8 No 4<br>
                    Landungsari Dau Malang<br>
                    WA 0822 44702525
                </div>

            </div>

        </div>

    </div>

    <div class="title-area">

    <div class="title-ornament">— ✦ —</div>

    <div class="title">
        Rekap Bulanan Semua Reseller
    </div>

    {{-- <div class="subtitle">
        {{ $bulanLabel }}
    </div> --}}

</div>

    <div class="summary-card">

        <div class="summary-label">
            Periode
        </div>

        <div class="summary-value">
            {{ $bulanLabel }}
        </div>

    </div>

    <table>

        <thead>
            <tr>
                <th>Nama Reseller</th>
                <th>Total Invoice</th>
                <th>Total Qty</th>
                <th>Total Penjualan</th>
            </tr>
        </thead>

        <tbody>

        @foreach($rekap as $item)

            <tr>

                <td>
                    {{ $item['nama_reseller'] }}
                </td>

                <td>
                    {{ number_format($item['total_invoice']) }}
                </td>

                <td>
                    {{ number_format($item['total_qty']) }}
                </td>

                <td>
                    Rp {{ number_format($item['total_harga'],0,',','.') }}
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>
        @php

        $grandInvoice = $rekap->sum('total_invoice');

        $grandQty = $rekap->sum('total_qty');

        $grandTotal = $rekap->sum('total_harga');

    @endphp

    <div class="grand-total-area">

    <div class="payment-summary">

        <div class="payment-summary-box">

            <div class="payment-row">
                <span class="payment-row-label">
                    Total Invoice
                </span>

                <span class="payment-row-value">
                    {{ number_format($grandInvoice) }}
                </span>
            </div>

            <div class="payment-row">
                <span class="payment-row-label">
                    Total Produk Terjual
                </span>

                <span class="payment-row-value">
                    {{ number_format($grandQty) }}
                </span>
            </div>

        </div>

    </div>

    <div class="grand-total-block">

        <div class="grand-total-label">
            Grand Total
        </div>

        <div class="grand-total-value">
            Rp {{ number_format($grandTotal, 0, ',', '.') }}
        </div>

    </div>

</div>

</div>

</body>
</html>
