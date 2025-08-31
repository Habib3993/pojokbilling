<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $payment->customer->name }}</title>
    <style>
        /* ... (CSS Anda tidak perlu diubah) ... */
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .container { padding: 10px; }
        .header-table, .content-table, .footer-table { width: 100%; border-collapse: collapse; }
        .header-table td { padding: 5px; vertical-align: top; }
        .logo { width: 80px; }
        .company-details { font-size: 11px; }
        .company-details strong { font-size: 14px; }
        .invoice-title { font-size: 24px; font-weight: bold; text-align: right; }
        .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .customer-info-table { width: 100%; }
        .customer-info-table td { padding: 2px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 6px; text-align: center; }
        .items-table th { background-color: #e0e0e0; font-weight: bold; }
        .items-table .text-left { text-align: left; }
        .summary-table { width: 40%; float: right; margin-top: 10px; }
        .summary-table td { padding: 5px; }
        .summary-table .label { text-align: left; }
        .summary-table .value { text-align: right; }
        .summary-table .total { font-weight: bold; }
        .footer-note { text-align: center; margin-top: 50px; font-size: 10px; font-weight: bold; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .history-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        .history-table th, .history-table td { border: 1px solid #000; padding: 5px; text-align: center; }
        .history-table th { background-color: #e0e0e0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        {{-- BAGIAN HEADER (TIDAK ADA PERUBAHAN) --}}
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <img src="{{ public_path('img/logo.png') }}" alt="Logo" class="logo">
                    <div class="company-details">
                        <strong>PT. SARANA OPTIMA BERDIKARI</strong><br>
                        Jl. Babatan 2, No. 4, Kepuhkembeng, Peterongan, Jombang<br>
                        Tlp: 0823-1023-1039
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="invoice-title">INVOICE</div>
                </td>
            </tr>
        </table>

        {{-- BAGIAN INFO PELANGGAN (TIDAK ADA PERUBAHAN) --}}
        <table class="content-table" style="margin-top: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="section-title">Ditagihkan Kepada</div>
                    <table class="customer-info-table">
                        <tr><td width="35%">Nama Pelanggan</td><td>: {{ $payment->customer->name }}</td></tr>
                        <tr><td>Alamat Pelanggan</td><td>: {{ $payment->customer->lokasi }}</td></tr>
                        <tr><td>Telephone</td><td>: {{ $payment->customer->phone }}</td></tr>
                        <tr><td>Siklus Penagihan</td><td>: Bulanan</td></tr>
                        <tr><td>Metode Pembayaran</td><td>: {{ $payment->payment_method }}</td></tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="section-title">Rincian Penerimaan</div>
                    <table class="customer-info-table">
                        <tr><td width="35%">Nomor Penerimaan</td><td>: INV-{{ $payment->id }}</td></tr>
                        <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <div class="section-title" style="margin-top: 20px;">Rincian Penyedia Internet</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>PAKET LAYANAN</th>
                    <th>KECEPATAN PAKET</th>
                    <th>DURASI PEMBAYARAN</th>
                    <th>MASA BERLAKU PAKET</th>
                    <th>JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    {{-- PENYEMPURNAAN 1: Menggunakan Null Safe Operator --}}
                    <td>{{ $payment->customer->package?->service_name ?? 'N/A' }}</td>
                    <td>{{ $payment->customer->package?->speed ?? 'N/A' }}</td>
                    <td>{{ $payment->duration_months }} Bulan</td>
                    <td>{{ \Carbon\Carbon::parse($payment->customer->active_until)->format('d-M-y') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- BAGIAN SUMMARY (TIDAK ADA PERUBAHAN) --}}
        <div class="clearfix">
            <table class="summary-table">
                <tr>
                    <td class="label"></td>
                    <td class="value">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">PPN 11%</td>
                    <td class="value">Rp {{ number_format($ppn, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label total">TOTAL</td>
                    <td class="value total">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="section-title" style="margin-top: 30px;">Riwayat Pembayaran</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>PERIODE</th>
                    {{-- PENYEMPURNAAN 2: Judul kolom lebih akurat --}}
                    <th>JUMLAH DIBAYAR</th>
                    <th>STATUS</th>
                    <th>PEMBAYARAN</th>
                    <th>TANGGAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->payment_date)->format('F Y') }}</td>
                        {{-- Data ini tetap amount karena ini adalah riwayat pembayaran --}}
                        <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        <td>Lunas</td>
                        <td>{{ $item->payment_method }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->payment_date)->format('d-m-Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Tidak ada riwayat pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- BAGIAN FOOTER (TIDAK ADA PERUBAHAN) --}}
        <div class="footer-note">
            INI ADALAH FAKTUR YANG DIBUAT OLEH KOMPUTER DAN TIDAK MEMERLUKAN TANDA TANGAN
        </div>
    </div>
</body>
</html>