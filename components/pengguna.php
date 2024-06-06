<?php
class PenggunaController
{
    private $model, $view;
    public function __construct()
    {
        $this->model = new PenggunaModel();
        $this->view = new PenggunaView();
    }
    public function login()
    {
        $this->model->login();
    }
    public function logout()
    {
        $this->model->logout();
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

class PenggunaModel
{

    public function logout()
    {
        global $app;

        unset($_SESSION["pengguna"]);
        header("Location:" . $app->config["site"]);
    }
    public function login()
    {
        global $app;

        $username = isset($_POST["username"]) ? $_POST["username"] : "";
        $password = isset($_POST["password"]) ? $_POST["password"] : "";

        $sql = "SELECT *
                FROM pengguna
                WHERE username=:username";
        $params = array(
            ":username" => $username
        );
        $result = $app->find($sql, $params);
        //var_dump($result);
        if ($result) {
            $success = password_verify($password, $result->password);
            if ($success) {
                $_SESSION["pengguna"] = new stdClass();
                $_SESSION["pengguna"]->username = $result->username;
                $_SESSION["pengguna"]->nama = $result->nama;
                $_SESSION["pengguna"]->level_akses = $result->level_akses;
                header("Location:" . $app->config["site"] . "/admin");
            } else {
                header("Location:" . $app->config["site"]);
            }
        } else {
            header("Location:" . $app->config["site"]);
        }
    }

    public function findAll()
    {
        global $app;

        $sql = "SELECT id, username, nama, level_akses FROM pengguna";
        $result = $app->findAll($sql);

        return $result;
    }
    public function find($id)
    {
        global $app;
        $sql = "SELECT * FROM pengguna WHERE id=:id";
        $params = array(":id" => $id);
        $result = $app->find($sql, $params);
        if (!$result) {
            $result = new stdClass();
            $result->id = 0;
            $result->username = "";
            $result->password = "";
            $result->nama = "";
            $result->level_akses = "Mahasiswa";
        }
        return $result;
    }
    public function save()
    {
        global $app;
        $id = intval($_REQUEST["id"]);
        $username = $_REQUEST["username"];
        $password = $_REQUEST["password"];
        $nama = $_REQUEST["nama"];
        $level_akses = $_REQUEST["level_akses"];

        if (empty($username) || empty($password) || empty($nama)) {
            header("Location:" . $app->config["site"] . "/admin/Pengguna/edit/" . $id . "?message=All fields are required");
            return;
        }

        if ($id == 0) {
            $sql = "INSERT INTO pengguna (username, password, nama, level_akses)
                    VALUES (:username, :password, :nama, :level_akses)";
            $params = array(
                ":username" => $username,
                ":password" => password_hash($password, PASSWORD_BCRYPT),
                ":nama" => $nama,
                ":level_akses" => $level_akses
            );
            $app->query($sql, $params);
        } else {
            $sql = "UPDATE pengguna
                    SET username=:username, password=:password, nama=:nama, level_akses=:level_akses
                    WHERE id=:id";
            $params = array(
                ":id" => $id,
                ":username" => $username,
                ":password" => password_hash($password, PASSWORD_BCRYPT),
                ":nama" => $nama,
                ":level_akses" => $level_akses
            );
            $app->query($sql, $params);
        }
        header("Location:" . $app->config["site"] . "/admin/Pengguna?message=Data successfully saved");
    }
    public function delete($id)
    {
        global $app;
        $sql = "DELETE FROM pengguna WHERE id=:id";
        $params = array(":id" => $id);
        $app->query($sql, $params);
        header("Location:" . $app->config["site"] . "/admin/Pengguna?message=Data successfully deleted");
    }
}

class PenggunaView
{
    public function edit($result)
    {
        global $app;
        ?>
        <form action="<?php echo $app->config["site"]; ?>/admin/Pengguna/save" method="post">
            <input type="hidden" name="id" value="<?php echo $result->id; ?>">
            <div class="pmd-card pmd-z-depth">
                <div class="pmd-card-title">
                    <h2 class="pmd-card-title-text typo-fill-secondary">Pengguna</h2>
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
                                <label for="username" class="control-label text-danger">
                                    Username*
                                </label>
                                <input class="form-control" name="username" maxlength="40"
                                    value="<?php echo $result->username; ?>" required>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                    <div class="group-fields clearfix row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="password" class="control-label text-danger">
                                    Password*
                                </label>
                                <input class="form-control" name="password" type="password" required>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                    <div class="group-fields clearfix row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="nama" class="control-label text-danger">
                                    Nama*
                                </label>
                                <input class="form-control" name="nama" maxlength="100" value="<?php echo $result->nama; ?>"
                                    required>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                    <div class="group-fields clearfix row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="level_akses" class="control-label text-danger">
                                    Level Akses*
                                </label>
                                <select class="form-control" name="level_akses">
                                    <option value="Administrator" <?php echo $result->level_akses == 'Administrator' ? 'selected' : ''; ?>>Administrator</option>
                                    <option value="Dosen" <?php echo $result->level_akses == 'Dosen' ? 'selected' : ''; ?>>Dosen
                                    </option>
                                    <option value="Mahasiswa" <?php echo $result->level_akses == 'Mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                                </select>
                                <span class="pmd-textfield-focused"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pmd-card-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-default" href="<?php echo $app->config["site"]; ?>/admin/Pengguna/index">Batal</a>
                </div>
            </div>
        </form>
        <?php
    }
    public function index($result)
    {
        global $app;
        ?>
        <div class="pmd-card pmd-z-depth">
            <div class="pmd-card-title">
                <h2 class="pmd-card-title-text typo-fill-secondary">Pengguna</h2>
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
                    <a class="btn btn-md btn-primary" href="<?php echo $app->config["site"]; ?>/admin/Pengguna/add">Tambah</a>
                </div>
                <div class="table-responsive">
                    <table id="example" class="table pmd-table table-hover table-striped display responsive" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th style="width:50px;">Aksi</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Level Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($result as $v) {
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $app->config["site"]; ?>/admin/Pengguna/edit/<?php echo $v->id; ?>"><i
                                                class="material-icons md-dark pmd-sm">edit</i></a>
                                        <a href="javascript:deleteRecord(<?php echo $v->id; ?>);"><i
                                                class="material-icons md-dark pmd-sm">delete</i></a>
                                    </td>
                                    <td><?php echo $v->username; ?></td>
                                    <td><?php echo $v->nama; ?></td>
                                    <td><?php echo $v->level_akses; ?></td>
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
                    window.open("<?php echo $app->config["site"] ?>/admin/Pengguna/delete/" + id, '_self');
                }
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
                        "info": " START - END of TOTAL ",
                        "sLengthMenu": "<span class='custom-select-title'>Rows per page:</span> <span class='custom-select'> MENU </span>",
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