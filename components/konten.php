<?php
class KontenController
{
    private $model, $view;

    public function __construct()
    {
        $this->model = new KontenModel();
        $this->view = new KontenView();
    }

    public function index()
    {
        $this->view->index($this->model->findAll());
    }

    public function add()
    {
        $this->view->edit($this->model->find(0));
    }

    public function edit($id)
    {
        $this->view->edit($this->model->find($id));
    }

    public function save()
    {
        $this->model->save();
    }

    public function delete($id)
    {
        $this->model->delete($id);
    }

    public function search($id)
    {
        $this->model->search($id);
    }
}

class KontenModel
{

    public function findPublishedContent() {
        global $app;

        $sql = "SELECT id,judul, kategori, tanggal, foto FROM konten WHERE publikasi = 1";
        $result = $app->findAll($sql);

        return $result;
    }

    public function findAll()
    {
        global $app;

        $sql = "SELECT * FROM konten";
        $result = $app->findAll($sql);

        return $result;
    }

    public function find($id)
    {
        global $app;

        $sql = "SELECT * FROM konten WHERE id=:id";
        $params = array(
            ":id" => $id
        );
        $result = $app->find($sql, $params);
        if (!$result) {
            $result = new stdClass();
            $result->id = 0;
            $result->judul = "";
            $result->kategori = "";
            $result->tanggal = "";
            $result->isi = "";
            $result->foto = "";
            $result->publikasi = 0;
        }

        return $result;
    }

    public function search($id)
    {
        global $app, $config;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $config["site"] . "/Api/getKonten/" . $id . "?json=1");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config["token"]
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
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

        $id = intval($_REQUEST["id"]);
        $judul = $_REQUEST["judul"];
        $kategori = $_REQUEST["kategori"];
        $tanggal = $_REQUEST["tanggal"];
        $isi = $_REQUEST["isi"];
        $foto = $_REQUEST["foto"];
        $publikasi = intval($_REQUEST["publikasi"]);

        if (empty($judul) || empty($kategori) || empty($tanggal) || empty($isi)) {
            header("Location:" . $app->config["site"] . "/admin/Konten/edit/" . $id . "?message=Semua field wajib diisi");
            return;
        }

        if ($id == 0) {
            $sql = "INSERT INTO konten (judul, kategori, tanggal, isi, foto, publikasi) VALUES (:judul, :kategori, :tanggal, :isi, :foto, :publikasi)";
            $params = array(
                ":judul" => $judul,
                ":kategori" => $kategori,
                ":tanggal" => $tanggal,
                ":isi" => $isi,
                ":foto" => $foto,
                ":publikasi" => $publikasi
            );
            $app->query($sql, $params);
        } else {
            $sql = "UPDATE konten SET judul=:judul, kategori=:kategori, tanggal=:tanggal, isi=:isi, foto=:foto, publikasi=:publikasi WHERE id=:id";
            $params = array(
                ":id" => $id,
                ":judul" => $judul,
                ":kategori" => $kategori,
                ":tanggal" => $tanggal,
                ":isi" => $isi,
                ":foto" => $foto,
                ":publikasi" => $publikasi
            );
            $app->query($sql, $params);
        }

        header("Location:" . $app->config["site"] . "/admin/Konten?message=Data berhasil disimpan");
    }

    public function delete($id)
    {
        global $app;

        $sql = "DELETE FROM konten WHERE id=:id";
        $params = array(
            ":id" => $id
        );
        $app->query($sql, $params);

        header("Location:" . $app->config["site"] . "/admin/Konten?message=Data berhasil dihapus");
    }
}

class KontenView
{
    public function edit($result)
    {
        global $app;
        ?>
        <form action="<?php echo $app->config["site"]; ?>/admin/Konten/save" method="post">
            <input type="hidden" name="id" value="<?php echo $result->id; ?>">
            <div class="pmd-card pmd-z-depth">
                <div class="pmd-card-title">
                    <h2 class="pmd-card-title-text typo-fill-secondary">Konten</h2>
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
                                <label for="judul" class="control-label text-danger">
                                    Judul*
                                </label>
                                <input class="form-control" name="judul" maxlength="100" value="<?php echo $result->judul; ?>"
                                    required autofocus>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="kategori" class="control-label text-danger">
                                    Kategori*
                                </label>
                                <input class="form-control" name="kategori" maxlength="100" value="<?php echo $result->kategori; ?>"
                                    required>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="tanggal" class="control-label text-danger">
                                    Tanggal*
                                </label>
                                <input type="date" class="form-control" name="tanggal" value="<?php echo $result->tanggal; ?>"
                                    required>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="isi" class="control-label text-danger">
                                    Isi*
                                </label>
                                <textarea class="form-control" name="isi" required><?php echo $result->isi; ?></textarea>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="foto" class="control-label text-danger">
                                    Foto
                                </label>
                                <input class="form-control" name="foto" maxlength="100" value="<?php echo $result->foto; ?>">
                                <span class="pmd-textfield-focused"></span>
                            </div>
                            <div class="form-group form-group-sm">
                                <label for="publikasi" class="control-label text-danger">
                                    Publikasi
                                </label>
                                <select class="form-control" name="publikasi" required>
                                    <option value="1" <?php if ($result->publikasi == 1) echo 'selected'; ?>>Ya</option>
                                    <option value="0" <?php if ($result->publikasi == 0) echo 'selected'; ?>>Tidak</option>
                                </select>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pmd-card-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-default" href="<?php echo $app->config["site"]; ?>/admin/Konten/index">Batal</a>
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
                <h2 class="pmd-card-title-text typo-fill-secondary">Konten</h2>
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
                    <a class="btn btn-md btn-primary" href="<?php echo $app->config["site"]; ?>/admin/Konten/add">Tambah</a>
                </div>
                <div class="table-responsive">
                    <table id="example" class="table pmd-table table-hover table-striped display responsive" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th style="width:100px;">Aksi</th>
                                <th style="width: 660px">Judul</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Publikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($result as $v) {
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Konten/edit/<?php echo $v->id; ?>"><i
                                                class="material-icons md-dark pmd-sm">edit</i></a>
                                        <a href="javascript:deleteRecord(<?php echo $v->id; ?>);"><i
                                                class="material-icons md-dark pmd-sm">delete</i></a>
                                        <a href="javascript:findRecord(<?php echo $v->id; ?>);"><i
                                                class="material-icons md-dark pmd-sm">search</i></a>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Konten/search/<?php echo $v->id; ?>"><i
                                                class="material-icons md-dark pmd-sm">help_outline</i></a>
                                    </td>
                                    <td><?php echo $v->judul; ?></td>
                                    <td><?php echo $v->kategori; ?></td>
                                    <td><?php echo $v->tanggal; ?></td>
                                    <td><?php echo ($v->publikasi == 1) ? 'Ya' : 'Tidak'; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
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
            function deleteRecord(id) {
                if (confirm("Hapus data")) {
                    window.open("<?php echo $app->config["site"] ?>/admin/Konten/delete/" + id, '_self');
                }
            }

            function findRecord(id) {
                $.getJSON("<?php echo $config["site"]; ?>/admin/Api/getKonten/"  + id, function(result){
                    //$("#data").html(result);
                    if (result.success) {
                        //console.log(result.data.nama);
                        alert(result.data.judul);
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
                    "paging": true,
                    "searching": true,
                    "language": {
                        "info": " _START_ - _END_ of _TOTAL_ ",
                        "sLengthMenu": "<span class='custom-select-title'>Rows per page:</span> <span class='custom-select'> _MENU_ </span>",
                        "sSearch": "",
                        "sSearchPlaceholder": "Search",
                        "paginate": {
                            "sNext": " ",
                            "sPrevious": " "
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