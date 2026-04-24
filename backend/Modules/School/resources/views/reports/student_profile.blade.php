<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .section-title { font-weight: bold; background: #f3f4f6; padding: 5px; margin-top: 15px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; }
        .label { width: 150px; color: #666; }
        .value { color: #000; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">DATA PROFIL SISWA</div>
        <div>{{ $school->name }}</div>
    </div>

    <div class="section-title">BIODATA SISWA</div>
    <table>
        <tr><td class="label">Nama Lengkap</td><td class="value">: {{ $student->full_name }}</td></tr>
        <tr><td class="label">NISN / NIS</td><td class="value">: {{ $student->nisn }} / {{ $student->nis }}</td></tr>
        <tr><td class="label">Tempat, Tgl Lahir</td><td class="value">: {{ $student->place_of_birth }}, {{ $student->date_of_birth?->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="value">: {{ $student->gender }}</td></tr>
        <tr><td class="label">Alamat</td><td class="value">: {{ $student->address }}</td></tr>
    </table>

    <div class="section-title">DATA ORANG TUA</div>
    <table>
        <tr><td class="label">Nama Ayah</td><td class="value">: {{ $student->father_name }}</td></tr>
        <tr><td class="label">Nama Ibu</td><td class="value">: {{ $student->mother_name }}</td></tr>
        <tr><td class="label">No. Telepon</td><td class="value">: {{ $student->phone }}</td></tr>
    </table>

    <div class="section-title">RIWAYAT AKADEMIK</div>
    <table>
        <tr><td class="label">Jurusan</td><td class="value">: {{ $student->department?->name ?? '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="value">: AKTIF</td></tr>
    </table>

    <div style="margin-top: 40px; text-align: right;">
        <p>Petugas Administrasi,</p>
        <div style="margin-top: 50px;">( ................................. )</div>
    </div>
</body>
</html>
