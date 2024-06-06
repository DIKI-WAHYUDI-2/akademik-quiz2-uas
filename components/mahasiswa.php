<?php
class MahasiswaController
{
    private $model, $view;

    public function __construct()
    {
        $this->model = new MahasiswaModel();
        $this->view = new MahasiswaView();
    }

    public function index()
    {
        $this->view->index($this->model->findAll());
    }

    public function add()
    {
        $this->view->edit($this->model->find(0));
    }

    public function edit($nim)
    {
        $this->view->edit($this->model->find($nim));
    }

    public function save()
    {
        $this->model->save();
    }

    public function delete($nim)
    {
        $this->model->delete($nim);
    }

    public function search($nim)
    {
        $this->model->search($nim);
    }
}
class MahasiswaModel
{
    public function findAll()
    {
        global $app;

        $sql = "SELECT m.*, j.nama as jurusan_nama
                FROM mahasiswa m
                LEFT JOIN jurusan j ON m.jurusan_id = j.id";
        $result = $app->findAll($sql);

        return $result;
    }

    public function find($nim)
    {
        global $app;

        $sql = "SELECT m.*, j.nama as jurusan_nama
                FROM mahasiswa m
                LEFT JOIN jurusan j ON m.jurusan_id = j.id
                WHERE nim = :nim";
        $params = array(
            ":nim" => $nim
        );
        $result = $app->find($sql, $params);
        if (!$result) {
            $result = new stdClass();
            $result->nim = "";
            $result->nama = "";
            $result->tempat_lahir = "";
            $result->tanggal_lahir = "";
            $result->jenis_kelamin = "";
            $result->jurusan_id = "";
            $result->tahun_masuk = "";
            $result->foto = "";
            $result->jurusan_nama = "";
        }

        return $result;
    }


    public function search($nim)
    {
        global $app, $config;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_URL, $config["site"]."/admin/Api/getMahasiswa/".$nim."?json=1"); 
        curl_setopt($ch, CURLOPT_URL, $config["site"] . "/Api/getMahasiswa/" . $nim . "?json=1");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config["token"]
        )
        );
        //curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        //var_dump($output);
        if (curl_errno($ch)) {
            $errNo = curl_errno($ch);
            $errMessage = curl_strerror($errNo);
            echo $errNo . " - " . $errMessage;
            return;
        }
        curl_close($ch);

        $json = json_decode($output);
        echo "<pre>" . print_r($json, true) . "</pre>";
    }

    public function save()
{
    global $app;

    $nim = $_REQUEST["nim"];
    $nama = $_REQUEST["nama"];
    $tempat_lahir = $_REQUEST["tempat_lahir"];
    $tanggal_lahir = $_REQUEST["tanggal_lahir"];
    $jenis_kelamin = $_REQUEST["jenis_kelamin"];
    $jurusan_id = $_REQUEST["jurusan_id"];
    $tahun_masuk = $_REQUEST["tahun_masuk"];
    $foto = $_REQUEST["foto"];

    if (empty($nama)) {
        header("Location:" . $app->config["site"] . "/admin/Mahasiswa/edit/" . $nim . "?message=Nama belum diisi");
        return;
    }

    $sql = "INSERT INTO mahasiswa (nim, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, jurusan_id, tahun_masuk, foto)
            VALUES (:nim, :nama, :tempat_lahir, :tanggal_lahir, :jenis_kelamin, :jurusan_id, :tahun_masuk, :foto)
            ON DUPLICATE KEY UPDATE nama=:nama, tempat_lahir=:tempat_lahir, tanggal_lahir=:tanggal_lahir, jenis_kelamin=:jenis_kelamin,
            jurusan_id=:jurusan_id, tahun_masuk=:tahun_masuk, foto=:foto";
    $params = array(
        ":nim" => $nim,
        ":nama" => $nama,
        ":tempat_lahir" => $tempat_lahir,
        ":tanggal_lahir" => $tanggal_lahir,
        ":jenis_kelamin" => $jenis_kelamin,
        ":jurusan_id" => $jurusan_id,
        ":tahun_masuk" => $tahun_masuk,
        ":foto" => $foto
    );
    $app->query($sql, $params);

    header("Location:" . $app->config["site"] . "/admin/Mahasiswa?message=Data berhasil disimpan");
}


    public function delete($nim)
    {
        global $app;

        $sql = "DELETE FROM mahasiswa
                WHERE nim=:nim";
        $params = array(
            ":nim" => $nim
        );
        $app->query($sql, $params);

        header("Location:" . $app->config["site"] . "/admin/Mahasiswa?message=Data berhasil dihapus");
    }
}
class MahasiswaView
{
    public function edit($result)
    {
        global $app;
        ?>
        <form action="<?php echo $app->config["site"]; ?>/admin/Mahasiswa/save" method="post">
            <input type="hidden" name="nim" value="<?php echo $result->nim; ?>">
            <div class="pmd-card pmd-z-depth">
                <div class="pmd-card-title">
                    <h2 class="pmd-card-title-text typo-fill-secondary">Mahasiswa</h2>
                </div>
                <?php
                if (isset($_REQUEST["message"])) {
                    ?>
                    <div class="alert alert-info"><?php echo $_REQUEST["message"]; ?></div>
                    <?php
                }
                ?>
                <div class="pmd-card-body">
                    <div class="group-fields clearfix row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="nim" class="control-label text-danger">NIM*</label>
                                <input class="form-control" name="nim" maxlength="11" value="<?php echo $result->nim; ?>" required autofocus>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="nama" class="control-label text-danger">Nama*</label>
                                <input class="form-control" name="nama" maxlength="30" value="<?php echo $result->nama; ?>" required>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="tempat_lahir" class="control-label text-danger">Tempat Lahir</label>
                                <input class="form-control" name="tempat_lahir" maxlength="25" value="<?php echo isset($result->tempat_lahir) ? $result->tempat_lahir : ''; ?>">
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="tanggal_lahir" class="control-label text-danger">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo $result->tanggal_lahir; ?>">
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="jenis_kelamin" class="control-label text-danger">Jenis Kelamin</label>
                                <select class="form-control" name="jenis_kelamin" required>
                                    <option value="Laki-laki" <?php if (isset($result->jenis_kelamin) && $result->jenis_kelamin == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php if (isset($result->jenis_kelamin) && $result->jenis_kelamin == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                                </select>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="jurusan_id" class="control-label text-danger">Jurusan</label>
                                <select class="form-control" name="jurusan_id" required>
                                    <?php
                                    $jurusan = $app->findAll("SELECT id, nama FROM jurusan");
                                    foreach ($jurusan as $j) {
                                        ?>
                                        <option value="<?php echo $j->id; ?>" <?php if (isset($result->jurusan_id) && $result->jurusan_id == $j->id) echo 'selected'; ?>><?php echo $j->nama; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="tahun_masuk" class="control-label text-danger">Tahun Masuk</label>
                                <input type="number" class="form-control" name="tahun_masuk" value="<?php echo $result->tahun_masuk; ?>">
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="foto" class="control-label text-danger">Foto</label>
                                <input class="form-control" name="foto" maxlength="40" value="<?php echo isset($result->foto) ? $result->foto : ''; ?>">
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pmd-card-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-default" href="<?php echo $app->config["site"]; ?>/admin/Mahasiswa/index">Batal</a>
                </div>
            </div>
        </form>
        <?php
    }

    public function index($result)
    {
        global $app, $config;
        ?>
        <div class="pmd-card pmd-z-depth">
            <div class="pmd-card-title">
                <h2 class="pmd-card-title-text typo-fill-secondary">Mahasiswa</h2>
            </div>
            <?php
            if (isset($_REQUEST["message"])) {
                ?>
                <div class="alert alert-info"><?php echo $_REQUEST["message"]; ?></div>
                <?php
            }
            ?>
            <div class="pmd-card-body">
                <div>
                    <a class="btn btn-md btn-primary" href="<?php echo $app->config["site"]; ?>/admin/Mahasiswa/add">Tambah</a>
                </div>
                <div class="table-responsive">
                    <table id="example" class="table pmd-table table-hover table-striped display responsive" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th style="width:100px;">Aksi</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Tempat Lahir</th>
                                <th>Tanggal Lahir</th>
                                <th>Jenis Kelamin</th>
                                <th>Jurusan</th>
                                <th>Tahun Masuk</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $v) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Mahasiswa/edit/<?php echo $v->nim; ?>"><i class="material-icons md-dark pmd-sm">edit</i></a>
                                        <a href="javascript:deleteRecord(<?php echo $v->nim; ?>);"><i class="material-icons md-dark pmd-sm">delete</i></a>
                                        <a href="javascript:findRecord(<?php echo $v->nim; ?>);"><i class="material-icons md-dark pmd-sm">search</i></a>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Mahasiswa/search/<?php echo $v->nim; ?>"><i class="material-icons md-dark pmd-sm">help_outline</i></a>
                                    </td>
                                    <td><?php echo $v->nim; ?></td>
                                    <td><?php echo $v->nama; ?></td>
                                    <td><?php echo $v->tempat_lahir; ?></td>
                                    <td><?php echo $v->tanggal_lahir; ?></td>
                                    <td><?php echo $v->jenis_kelamin; ?></td>
                                    <td><?php echo $v->jurusan_nama; ?></td>
                                    <td><?php echo $v->tahun_masuk; ?></td>
                                    <td><?php echo $v->foto; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
        <style>
            #example_wrapper .row {
                margin-right: 0px;
            }
        </style>
        <script>
            function deleteRecord(nim) {
                if (confirm("Hapus data")) {
                    window.open("<?php echo $app->config["site"] ?>/admin/Mahasiswa/delete/" + nim, '_self');
                }
            }

            function findRecord(nim) {
                $.getJSON("<?php echo $config["site"]; ?>/admin/Api/getMahasiswa/"  + nim, function(result){
                    if (result.success) {
                        alert(result.data.nama);
                    } else {
                        alert(result.message);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                $('#example').DataTable({
                    responsive: {
                        details: {
                            type: 'column',
                            target: 'tr'
                        }
                    },
                    order: [1, 'asc'],
                    bFilter: true,
                    bLengthChange: true,
                    pagingType: "simple",
                    paging: true,
                    searching: true,
                    language: {
                        info: " _START_ - _END_ of _TOTAL_ ",
                        sLengthMenu: "<span class='custom-select-title'>Rows per page:</span> <span class='custom-select'> _MENU_ </span>",
                        sSearch: "",
                        sSearchPlaceholder: "Search",
                        paginate: {
                            sNext: " ",
                            sPrevious: " "
                        },
                    },
                    dom:
                        "<'pmd-card-title'<'data-table-title'><'search-paper pmd-textfield'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'pmd-card-footer' <'pmd-datatable-pagination' l i p>>",
                });
            });
        </script>
        <?php
    }
}

?>