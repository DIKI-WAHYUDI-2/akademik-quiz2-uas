<?php
class ApiController
{
    private $model;
    public function __construct()
    {
        $this->model = new ApiModel();
    }
    public function index()
    {
        $this->model->index();
    }
    public function getAllFakultas()
    {
        $this->model->getAllFakultas();
    }
    public function getFakultas($id)
    {
        $this->model->getFakultas($id);
    }
}
class ApiModel
{
    public function getAuthorizationBearerToken()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    public function response($success, $message, $data = null)
    {
        $response = new stdClass();
        $response->success = $success;
        $response->message = $message;
        $response->data = $data;

        header("Content-Type: application/json");
        return json_encode($response);
    }
    public function index()
    {
        global $config;

        $token = $this->getAuthorizationBearerToken();
        if ($token != $config["token"]) {
            echo $this->response(false, "Akses token salah");
            return;
        }

        echo $this->response(false, "Akses token benar");
    }
    public function getAllFakultas()
    {
        global $app, $config;

        $token = $this->getAuthorizationBearerToken();
        if ($token != $config["token"]) {
            echo $this->response(false, "Akses token salah");
            return;
        }

        $sql = "SELECT *
                FROM fakultas";
        $result = $app->findAll($sql);

        echo $this->response(true, "", $result);
    }

    public function getAllJurusan()
    {
        global $app, $config;

        $token = $this->getAuthorizationBearerToken();
        if ($token != $config["token"]) {
            echo $this->response(false, "Akses token salah");
            return;
        }

        $sql = "SELECT *
                FROM jurusan";
        $result = $app->findAll($sql);

        echo $this->response(true, "", $result);
    }

    public function getFakultas($id)
    {
        global $app, $config;

        $token = $this->getAuthorizationBearerToken();

        /*if ($token != $config["token"]) {
            echo $this->response(false, "Akses token salah");
            return;
        }*/

        $sql = "SELECT *
                FROM fakultas
                WHERE id=:id";
        $params = array(
            ":id" => $id
        );
        $result = $app->find($sql, $params);

        if ($result) {
            echo $this->response(true, "", $result);
        } else {
            echo $this->response(false, "Data tidak ditemukan");
        }
    }

    public function getJurusan($id)
    {
        global $app, $config;

        $token = $this->getAuthorizationBearerToken();

        /*if ($token != $config["token"]) {
            echo $this->response(false, "Akses token salah");
            return;
        }*/

        $sql = "SELECT *
                FROM jurusan
                WHERE id=:id";
        $params = array(
            ":id" => $id
        );
        $result = $app->find($sql, $params);

        if ($result) {
            echo $this->response(true, "", $result);
        } else {
            echo $this->response(false, "Data tidak ditemukan");
        }
    }
}
?>