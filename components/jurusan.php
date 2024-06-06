<?php
class JurusanController
{
    private $model, $view;

    public function __construct()
    {
        $this->model = new JurusanModel();
        $this->view = new JurusanView();
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
}

class JurusanModel
{
    public function findAll()
    {
        global $app;

        $sql = "SELECT jurusan.id, jurusan.nama, fakultas.nama AS nama_fakultas
                FROM jurusan
                JOIN fakultas ON jurusan.fakultas_id = fakultas.id";
        $result = $app->findAll($sql);

        return $result;
    }

    public function find($id)
    {
        global $app;

        $sql = "SELECT *
                FROM jurusan
                WHERE id = :id";
        $params = array(":id" => $id);
        $result = $app->find($sql, $params);

        return $result;
    }

    public function save()
    {
        global $app;

        $id = intval($_REQUEST["id"]);
        $nama = $_REQUEST["nama"];
        $fakultas_id = $_REQUEST["fakultas"];

        if (empty($nama) || empty($fakultas_id)) {
            header("Location:" . $app->config["site"] . "/admin/Jurusan/edit/" . $id . "?message=Nama atau Fakultas belum diisi");
            return;
        }

        if ($id == 0) {
            $sql = "INSERT INTO jurusan (nama, fakultas_id) VALUES (:nama, :fakultas_id)";
            $params = array(":nama" => $nama, ":fakultas_id" => $fakultas_id);
            $app->query($sql, $params);
        } else {
            $sql = "UPDATE jurusan SET nama = :nama, fakultas_id = :fakultas_id WHERE id = :id";
            $params = array(":id" => $id, ":nama" => $nama, ":fakultas_id" => $fakultas_id);
            $app->query($sql, $params);
        }

        header("Location:" . $app->config["site"] . "/admin/Jurusan?message=Data berhasil disimpan");
    }

    public function delete($id)
    {
        global $app;

        $sql = "DELETE FROM jurusan WHERE id = :id";
        $params = array(":id" => $id);
        $app->query($sql, $params);

        header("Location:" . $app->config["site"] . "/admin/Jurusan?message=Data berhasil dihapus");
    }
}

class JurusanView
{
    public function edit($result)
    {
        global $app;
        ?>
        <form action="<?php echo $app->config["site"]; ?>/admin/Jurusan/save" method="post">
            <input type="hidden" name="id" value="<?php echo isset($result->id) ? $result->id : ''; ?>">
            <div class="pmd-card pmd-z-depth">
                <div class="pmd-card-title">
                    <h2 class="pmd-card-title-text typo-fill-secondary">Jurusan</h2>
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
                                <label for="nama" class="control-label text-danger">Nama*</label>
                                <input class="form-control" name="nama" maxlength="40"
                                    value="<?php echo isset($result->nama) ? $result->nama : ''; ?>" required autofocus>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>

                    <div class="group-fields clearfix row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="fakultas" class="control-label text-danger">Fakultas*</label>

                                <?php
                                $sql = "SELECT * FROM fakultas ORDER BY nama";
                                $resultFakultas = $app->findAll($sql);
                                ?>

                                <select name="fakultas" class="form-control" required>
                                    <?php
                                    foreach ($resultFakultas as $v) {
                                        echo '<option value="' . $v->id . '"';
                                        if (isset($result->fakultas_id) && $result->fakultas_id == $v->id) {
                                            echo ' selected';
                                        }
                                        echo '>' . $v->nama . '</option>';
                                    }
                                    ?>
                                </select>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pmd-card-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-default" href="<?php echo $app->config["site"]; ?>/admin/Jurusan/index">Batal</a>
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
                <h2 class="pmd-card-title-text typo-fill-secondary">Jurusan</h2>
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
                    <a class="btn btn-md btn-primary" href="<?php echo $app->config["site"]; ?>/admin/Jurusan/add">Tambah</a>
                </div>
                <div class="table-responsive">
                    <table id="example" class="table pmd-table table-hover table-striped display responsive" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th style="width:150px;">Aksi</th>
                                <th>Nama</th>
                                <th>Fakultas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($result as $v) {
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Jurusan/edit/<?php echo $v->id; ?>"><i
                                                class="material-icons md-dark pmd-sm">edit</i></a>
                                        <a href="javascript:deleteRecord(<?php echo $v->id; ?>);"><i
                                                class="material-icons md-dark pmd-sm">delete</i></a>
                                        <a href="javascript:findRecord(<?php echo $v->id; ?>);"><i
                                                class="material-icons md-dark pmd-sm">search</i></a>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Jurusan/search/<?php echo $v->id; ?>"><i
                                                class="material-icons md-dark pmd-sm">help_outline</i></a>
                                    </td>
                                    <td><?php echo $v->nama; ?></td>
                                    <td><?php echo $v->nama_fakultas; ?></td>
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
                    window.open("<?php echo $app->config["site"]; ?>/admin/Jurusan/delete/" + id, '_self');
                }
            }

            function findRecord(id) {
                $.getJSON("<?php echo $config["site"]; ?>/admin/Api/getJ urusan/" + id, function(result) {
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
                    dom: "<'pmd-card-title'<'data-table-title'><'search-paper pmd-textfield'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'pmd-card-footer'<'pmd-datatable-pagination' l i p>>",
                });
            });
        </script>
        <?php
    }
}
?>