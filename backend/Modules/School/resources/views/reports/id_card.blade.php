<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            size: 86mm 54mm;
            margin: 0;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }
        .card {
            width: 86mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
        }
        .header {
            padding: 5px 10px;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            border-bottom: 2px solid #3b82f6;
        }
        .school-name {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .content {
            padding: 10px;
            display: flex;
        }
        .photo {
            width: 20mm;
            height: 25mm;
            background: #cbd5e1;
            border: 1px solid white;
            margin-right: 10px;
        }
        .info {
            flex: 1;
        }
        .name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #3b82f6;
        }
        .detail {
            font-size: 8px;
            margin-bottom: 1px;
        }
        .label {
            color: #94a3b8;
        }
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 3px 10px;
            font-size: 7px;
            background: #3b82f6;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="school-name">{{ $school->name }}</span>
        </div>
        <div class="content">
            <div class="photo"></div>
            <div class="info">
                <div class="name">{{ $student->full_name }}</div>
                <div class="detail"><span class="label">NISN:</span> {{ $student->nisn }}</div>
                <div class="detail"><span class="label">TTL:</span> {{ $student->birth_place }}, {{ $student->birth_date?->format('d/m/Y') }}</div>
                <div class="detail"><span class="label">Alamat:</span> {{ substr($student->address, 0, 50) }}...</div>
                <div style="margin-top: 10px; text-align: right;">
                    <barcode code="{{ $verificationUrl }}" type="QR" size="0.6" error="M" />
                </div>
            </div>
        </div>
        <div class="footer">
            KARTU IDENTITAS PELAJAR - BERLAKU SELAMA MENJADI SISWA
        </div>
    </div>
</body>
</html>
