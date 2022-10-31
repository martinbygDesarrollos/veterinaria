<?php

require_once '\xampp\htdocs\veterinarianan\src\config.php';

$backup_file = DB_DB. "-" .date("d"). ".sql";

$arrayErrores = array();

array_push($arrayErrores, shell_exec("mysqldump --opt -h ".DB_HOST." -u ".DB_USR." -p".DB_PASS."  -v ".DB_DB. " > ".$backup_file) );

// inicio conexiono serv ftp
$ftp = ftp_connect(FTP_SERVER);
// login serv
$login_result = ftp_login($ftp, FTP_SERVER_USER, FTP_SERVER_PASS);
// ftp_put funcion especifica para enviar archivos
array_push($arrayErrores, ftp_put($ftp, "/users/respaldos/home/bygmysql/".$backup_file, $backup_file, FTP_ASCII));
// cerrar conex ftp
ftp_close($ftp);

var_dump($arrayErrores);
?>