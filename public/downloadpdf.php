<?php
include_once "../src/config.php";

$file_name = $_GET['n'];

header('Content-Type: application/pdf');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . basename($file_name.".pdf") . "\"");
header('Pragma: no-cache');

readfile(PATH_IMPRIMIBLES.$file_name.".pdf");

?>