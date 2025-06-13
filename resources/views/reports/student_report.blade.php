<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analisis Emosi Siswa - Format Surat</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif; /* Font klasik untuk surat */
            color: #222;
            line-height: 1.7; /* Spasi baris yang lebih longgar */
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            font-size: 11pt; /* Ukuran font standar untuk surat */
        }
        .letter-container {
            padding: 40px 60px; /* Padding lebih besar untuk kesan formal */
            max-width: 800px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #d0d0d0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); /* Shadow yang lebih jelas */
            border-radius: 5px; /* Sedikit radius untuk sudut */
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            margin: 0;
            color: #1a5a8e; /* Warna biru gelap */
            font-size: 1.8em; /* Ukuran H1 lebih kecil, fokus pada keseriusan */
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #1a5a8e;
            padding-bottom: 10px;
            display: inline-block; /* Agar border hanya sepanjang teks */
        }
        .header p {
            margin: 5px 0 0;
            font-size: 0.9em;
            color: #555;
        }
        .address-block, .date-block {
            margin-bottom: 20px;
        }
        .date-block {
            text-align: right;
        }
        .salutation {
            margin-bottom: 25px;
        }
        .content-paragraph {
            margin-bottom: 15px;
            text-align: justify;
        }
        /* CSS baru untuk tabel informasi formal */
        .info-table-formal {
            width: 100%;
            margin-bottom: 25px; /* Margin bawah sedikit lebih besar */
            border-collapse: collapse; /* Menghilangkan spasi antar sel */
        }
        .info-table-formal td {
            padding: 5px 0; /* Padding vertikal saja, horizontal diatur di label-column */
            vertical-align: top; /* Pastikan konten sel sejajar di atas */
        }
        .info-table-formal .label-column {
            width: 220px; /* Lebar tetap untuk kolom label agar align rapi */
            padding-right: 15px; /* Spasi antara label dan tanda titik dua */
        }
        .info-table-formal strong {
            color: #333;
        }
        .section-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #1a5a8e;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #ccc; /* Garis putus-putus */
            padding-bottom: 5px;
        }
        .chart-container {
            text-align: center;
            margin-top: 25px;
            background-color: #fcfcfc;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
        }
        .chart-container img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            border-radius: 3px;
        }
        .closing, .signature {
            margin-top: 35px;
            text-align: left;
        }
        .signature {
            margin-top: 50px; /* Ruang untuk tanda tangan */
        }
        .important-note {
            font-weight: bold;
            color: #a00; /* Warna merah gelap untuk penekanan */
        }
        .disclaimer {
            font-size: 0.85em;
            color: #888;
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="letter-container">
        <div class="header">
            <h1>Laporan Analisis Emosi Siswa</h1>
            <p>Sistem Aplikasi MoodVis</p>
        </div>

        <div class="date-block">
            Bandung, {{ \Carbon\Carbon::parse($printDate)->isoFormat('D MMMM YYYY') }}
        </div>

        <div class="address-block">
            Kepada Yth. Bapak/Ibu Wali Murid<br>
            Sdr/i. {{ $studentName ?? 'Nama Siswa/i' }}<br>
            Di Tempat
        </div>

        <div class="salutation">
            Dengan hormat,
        </div>

        <div class="content-paragraph">
            Melalui surat laporan ini, kami dengan bangga menyampaikan hasil analisis emosi ananda {{ $studentName ?? 'siswa/i' }} yang telah terekam secara otomatis oleh Sistem Aplikasi MoodVis kami selama periode pelaporan <span style="font-weight: bold;">{{ $reportPeriod ?? 'belum tersedia' }}</span>. Laporan ini disusun sebagai bentuk transparansi dan upaya kami dalam memahami serta mendukung perkembangan emosional setiap siswa/i.
        </div>

        <div class="section-title">Informasi Umum Siswa/i</div>
        <table class="info-table-formal">
            <tr>
                <td class="label-column"><strong>Nama Lengkap Siswa/i</strong></td>
                <td>: {{ $studentName ?? 'Belum Teridentifikasi' }}</td>
            </tr>
            <tr>
                <td class="label-column"><strong>Periode Data Analisis</strong></td>
                <td>: {{ $reportPeriod ?? 'Periode Tidak Tersedia' }}</td>
            </tr>
            <tr>
                <td class="label-column"><strong>Tanggal Pencetakan Laporan</strong></td>
                <td>: {{ \Carbon\Carbon::parse($printDate)->isoFormat('D MMMM YYYY [pukul] HH:mm') }} WIB</td>
            </tr>
        </table>

        <div class="section-title">Ringkasan Utama Perkembangan Emosi</div>
        <div class="content-paragraph">
            Berdasarkan kompilasi data yang cermat dari catatan harian emosi, kami mengidentifikasi bahwa <span class="important-note">emosi <span style="text-transform: uppercase;">{{ ucfirst($summaryEmotion) ?? 'belum ada data yang cukup' }}</span> merupakan emosi dominan</span> yang paling sering terekam dari ananda {{ $studentName ?? 'siswa/i' }} selama periode pengamatan ini. Temuan ini menyediakan wawasan awal yang krusial mengenai kecenderungan emosional yang diperlihatkan oleh ananda. Kami senantiasa berkomitmen untuk memantau tren ini lebih lanjut dan siap untuk memberikan bimbingan serta dukungan yang diperlukan untuk membantu ananda mengelola dan mengembangkan emosi secara positif.
        </div>

        <div class="section-title">Visualisasi Tren Frekuensi Emosi</div>
        <div class="content-paragraph">
            Untuk memberikan gambaran yang lebih visual dan mudah dipahami mengenai fluktuasi emosi ananda, berikut kami lampirkan grafik tren frekuensi emosi selama periode pelaporan.
        </div>
        <div class="chart-container">
            @if($chartImageBase64)
                <img src="{{ $chartImageBase64 }}" alt="Grafik Tren Emosi Siswa">
            @else
                <p>Mohon maaf, tidak ada data yang memadai untuk menghasilkan grafik tren emosi pada periode ini. Pastikan ananda telah melakukan pencatatan emosi secara rutin.</p>
            @endif
        </div>

        <div class="closing">
            Demikian laporan analisis emosi ini kami sampaikan. Kami berharap laporan ini dapat menjadi bahan diskusi dan evaluasi yang konstruktif bagi Bapak/Ibu Wali Murid dan ananda sekalian.
        </div>

        <div class="signature">
            Hormat kami,<br>
            Tim MoodVis<br>
            [Nama Sekolah/Institusi, jika relevan]<br>
            <br><br><br>
            (Tanda Tangan & Nama Jelas)<br>
            <span style="font-size: 0.9em; color: #555;">[Posisi/Jabatan, misal: Konselor Sekolah]</span>
        </div>

        <div class="disclaimer">
            *Laporan ini dihasilkan secara otomatis oleh Sistem Aplikasi MoodVis dan berfungsi sebagai panduan awal. Untuk interpretasi yang lebih mendalam dan konseling personal, sangat disarankan untuk berkonsultasi langsung dengan konselor atau psikolog sekolah.
        </div>
    </div>
</body>
</html>
