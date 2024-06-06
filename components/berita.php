<?php
include_once 'konten.php';
include_once 'beranda.php';

// Dapatkan ID konten dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Instansiasi model Konten
$model = new KontenModel();

// Cari konten berdasarkan ID
$konten = $model->find($id);

// Tampilkan konten jika ditemukan
if ($konten->id != 0) {
    ?>
    <div class="container">
        <h2><?php echo $konten->judul; ?></h2>
        <p>Kategori: <?php echo $konten->kategori; ?></p>
        <p>Tanggal: <?php echo $konten->tanggal; ?></p>
        <img src="<?php echo $konten->foto; ?>" alt="Foto Konten">
        <p><?php echo $konten->isi; ?></p>
    </div>
    <?php
} else {

}
?>