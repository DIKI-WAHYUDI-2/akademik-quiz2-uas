<?php
class FakultasController
{
    private $model, $view;

    public function __construct()
    {
        $this->model = new FakultasModel();
        $this->view = new FakultasView();
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
class FakultasModel
{
    public function findAll()
    {
        global $app;

        $sql = "SELECT *
                FROM fakultas";
        $result = $app->findAll($sql);

        return $result;
    }

    public function find($id)
    {
        global $app;

        $sql = "SELECT *
                FROM fakultas
                WHERE id=:id";
        $params = array(
            ":id" => $id
        );
        $result = $app->find($sql, $params);
        if (!$result) {
            $result = new stdClass();
            $result->id = 0;
            $result->nama = "";
        }

        return $result;
    }

    public function search($id)
    {
        global $app, $config;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_URL, $config["site"]."/admin/Api/getFakultas/".$id."?json=1"); 
        curl_setopt($ch, CURLOPT_URL, $config["site"] . "/Api/getFakultas/" . $id . "?json=1");
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

        $id = intval($_REQUEST["id"]);
        $nama = $_REQUEST["nama"];

        if (empty($nama)) {
            header("Location:" . $app->config["site"] . "/admin/Fakultas/edit/" . $id . "?message=Nama belum diisi");
            return;
        }

        if ($id == 0) {
            $sql = "INSERT INTO fakultas (nama)
                    VALUES (:nama)";
            $params = array(
                ":nama" => $nama
            );
            $app->query($sql, $params);
        } else {
            $sql = "UPDATE fakultas
                    SET nama=:nama
                    WHERE id=:id";
            $params = array(
                ":id" => $id,
                ":nama" => $nama
            );
            $app->query($sql, $params);
        }

        header("Location:" . $app->config["site"] . "/admin/Fakultas?message=Data berhasil disimpan");
    }

    public function delete($id)
    {
        global $app;

        $sql = "DELETE FROM fakultas
                WHERE id=:id";
        $params = array(
            ":id" => $id
        );
        $app->query($sql, $params);

        header("Location:" . $app->config["site"] . "/admin/Fakultas?message=Data berhasil dihapus");
    }
}
class FakultasView
{
    public function edit($result)
    {
        global $app;
        ?>
        <form action="<?php echo $app->config["site"]; ?>/admin/Fakultas/save" method="post">
            <input type="hidden" name="id" value="<?php echo $result->id; ?>">
            <div class="pmd-card pmd-z-depth">
                <div class="pmd-card-title">
                    <h2 class="pmd-card-title-text typo-fill-secondary">Fakultas</h2>
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
                                <label for="nama" class="control-label text-danger">
                                    Nama*
                                </label>
                                <input class="form-control" name="nama" maxlength="40" value="<?php echo $result->nama; ?>"
                                    required autofocus>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pmd-card-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-default" href="<?php echo $app->config["site"]; ?>/admin/Fakultas/index">Batal</a>
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
                <h2 class="pmd-card-title-text typo-fill-secondary">Fakultas</h2>
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
                    <a class="btn btn-md btn-primary" href="<?php echo $app->config["site"]; ?>/admin/Fakultas/add">Tambah</a>
                </div>
                <div class="table-responsive">
                    <table id="example" class="table pmd-table table-hover table-striped display responsive" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th style="width:100px;">Aksi</th>
                                <th>Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($result as $v) {
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Fakultas/edit/<?php echo $v->id; ?>"><i
                                                class="material-icons md-dark pmd-sm">edit</i></a>
                                        <a href="javascript:deleteRecord(<?php echo $v->id; ?>);"><i
                                                class="material-icons md-dark pmd-sm">delete</i></a>
                                        <a href="javascript:findRecord(<?php echo $v->id; ?>);"><i
                                                class="material-icons md-dark pmd-sm">search</i></a>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Fakultas/search/<?php echo $v->id; ?>"><i
                                                class="material-icons md-dark pmd-sm">help_outline</i></a>
                                    </td>
                                    <td><?php echo $v->nama; ?></td>
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
                    window.open("<?php echo $app->config["site"] ?>/admin/Fakultas/delete/" + id, '_self');
                }
            }

            function findRecord(id) {
                $.getJSON("<?php echo $config["site"]; ?>/admin/Api/getFakultas/"  + id, function(result){
                    //$("#data").html(result);
                    if (result.success) {
                        //console.log(result.data.nama);
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
?>