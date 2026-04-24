<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px double black;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        td {
            padding: 5px;
            vertical-align: top;
        }
        .signature {
            float: right;
            width: 200px;
            margin-top: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0">{{ strtoupper($school->foundation_name) }}</h2>
        <h1 style="margin: 0">{{ strtoupper($school->name) }}</h1>
        <p style="margin: 0">{{ $school->address }}</p>
    </div>

    <div class="title">SURAT KETERANGAN LULUS</div>

    <p>Kepala {{ $school->name }}, dengan ini menerangkan bahwa:</p>
    
    <table>
        <tr><td width="150">Nama</td><td width="10">:</td><td>{{ $student->full_name }}</td></tr>
        <tr><td>Tempat, Tgl Lahir</td><td>:</td><td>{{ $student->birth_place }}, {{ $student->birth_date?->format('d F Y') }}</td></tr>
        <tr><td>NISN / NIS</td><td>:</td><td>{{ $student->nisn }} / {{ $student->nis }}</td></tr>
        <tr><td>Kompetensi Keahlian</td><td>:</td><td>{{ $student->department?->name }}</td></tr>
    </table>

    <p style="text-align: justify">
        Berdasarkan hasil Rapat Pleno Dewan Pendidik tentang Penentuan Kelulusan, yang bersangkutan dinyatakan 
        <b>LULUS</b> dari satuan pendidikan {{ $school->name }} tahun pelajaran {{ $school->activeAcademicYear?->name }}.
    </p>

    <div class="signature">
        {{ $school->kabupaten_kota }}, {{ date('d F Y') }}<br>
        Kepala Sekolah,<br><br><br><br>
        <b>{{ $school->principal_name }}</b><br>
        NIP. {{ $school->principal_nip ?? '........................' }}
        <div style="margin-top: 20px;">
            <barcode code="{{ $verificationUrl }}" type="QR" size="0.8" error="M" /><br>
            <span style="font-size: 8px; font-family: sans-serif;">Pindai untuk verifikasi keaslian dokumen</span>
        </div>
    </div>
</body>
</html>
