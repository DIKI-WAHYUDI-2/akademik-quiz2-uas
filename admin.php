<?php
include_once __DIR__."/includes/config.php";
include_once __DIR__."/includes/class.php";

$app = new App($config);

if (!isset($_SESSION["pengguna"])) {
    header("Location:".$app->config["site"]);
}

ob_start();
$content = $app->loadComponent();
$component = isset($_REQUEST["com"]) ? $_REQUEST["com"] : "Beranda";
if ($component != "Api") {
    include_once $app->config["server"]."/webpages/administrator.php";
} else {
    echo $content;
}
ob_end_flush();
?>