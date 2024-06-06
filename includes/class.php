<?php
class App {
    public $config;
    public $connection;

    public function __construct($config) {
        $this->config = $config;

        session_name("selasa");
        session_start();

        try {
            $this->connection = new PDO($config["driver"].":host=".$config["host"].";port=".$config["port"].";dbname=".$config["database"], $config["user"], $config["password"], array());
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            echo $ex->getMessage();
            exit();
        }
    }

    public function loadComponent() {
        $component = isset($_REQUEST["com"]) ? $_REQUEST["com"] : "Beranda";
        $task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "index";

        $path = $this->config["server"]."/components/".strtolower($component).".php";
        include_once $path;
        $controllerName = $component."Controller";
        $controller = new $controllerName();

        if (isset($_REQUEST["id"])) {
            $controller->{$task}($_REQUEST["id"]);
        } else {
            $controller->{$task}();
        }
        
        $html = ob_get_contents();
        ob_clean();
        return $html;
    }

    public function find($sql, $params = array()) {
        $result = null;
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            $stmt->execute($params);
            $result = $stmt->fetch();
            $stmt->closeCursor();
        } catch (PDOException $ex) {
            echo $ex->getMessage();
            exit();
        }
        return $result;
    }

    public function findAll($sql, $params = array()) {
        $result = array();
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            $stmt->execute($params);
            while (($obj = $stmt->fetch()) == true) {
                $result[] = $obj;
            }
            $stmt->closeCursor();
        } catch (PDOException $ex) {
            echo $ex->getMessage();
            exit();
        }
        return $result;
    }

    public function query($sql, $params) {
        $affectedRows = 0;
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_OBJ);
            $stmt->execute($params);
            $affectedRows = $stmt->rowCount();
            $stmt->closeCursor();
        } catch (PDOException $ex) {
            echo $ex->getMessage();
            exit();
        }
        return $affectedRows;
    }
}
?>