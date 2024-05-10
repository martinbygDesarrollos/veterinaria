<?php
include_once "../src/config.php";

$path = $_GET['path'];
$category = $_GET['category'];

header('Content-Type: '.mime_content_type(PATH_ARCHIVOS.$category.$path));
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . pathinfo(PATH_ARCHIVOS.$category.$path)['basename'] . "\"");
header('Pragma: no-cache');

readfile(PATH_ARCHIVOS.$category.$path);

?>