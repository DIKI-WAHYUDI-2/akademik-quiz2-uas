<?php
include_once __DIR__."/includes/config.php";
include_once __DIR__."/includes/class.php";

$app = new App($config);

ob_start();
$content = $app->loadComponent();

$component = isset($_REQUEST["com"]) ? $_REQUEST["com"] : "Beranda";
if ($component == "Beranda") {
    include_once $app->config["server"]."/webpages/login.php";
} else {
    echo $content;
}
ob_end_flush();
?>