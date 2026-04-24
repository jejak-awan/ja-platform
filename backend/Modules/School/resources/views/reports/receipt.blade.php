<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 18px; font-bold: true; }
        .content { margin-bottom: 20px; }
        .row { display: flex; margin-bottom: 5px; }
        .label { width: 120px; color: #555; }
        .value { font-weight: bold; }
        .footer { margin-top: 50px; text-align: right; }
        .signature { margin-top: 60px; border-top: 1px solid #000; width: 200px; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">KWITANSI PEMBAYARAN</div>
        <div>{{ $school->name }}</div>
    </div>

    <div class="content">
        <table>
            <tr>
                <td class="label">No. Transaksi</td>
                <td>: <span class="value">{{ $transaction->reference_number ?? ('TRX-' . $transaction->id) }}</span></td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td>: <span class="value">{{ $transaction->payment_date->format('d F Y') }}</span></td>
            </tr>
            <tr>
                <td class="label">Siswa</td>
                <td>: <span class="value">{{ $transaction->bill->student->full_name }}</span></td>
            </tr>
            <tr>
                <td class="label">Untuk Pembayaran</td>
                <td>: <span class="value">{{ $transaction->bill->feeType->name }}</span></td>
            </tr>
            <tr>
                <td class="label">Jumlah</td>
                <td>: <span class="value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span></td>
            </tr>
            <tr>
                <td class="label">Metode</td>
                <td>: <span class="value">{{ $transaction->payment_method }}</span></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</div>
        <div class="signature">Petugas Keuangan</div>
    </div>
</body>
</html>
