<?php
//RESPALDAR BASE DE DATOS EN WINDOWS
require_once '\xampp\htdocs\veterinarianan\src\config.php'; //DATOS DE LA BASE DE DATOS

$backup_file = DB_DB. "-" .date("d"). ".sql";

$result = shell_exec("mysqldump --opt -h ".DB_HOST." -u ".DB_USR." -p".DB_PASS."  -v ".DB_DB. " > ".$backup_file);
error_log("RESPALDO - DUMP RESULTADO: ".$result);

// inicio conexiono serv ftp
$ftp = ftp_connect(FTP_SERVER);
// login serv
$login_result = ftp_login($ftp, FTP_SERVER_USER, FTP_SERVER_PASS);
// ftp_put funcion especifica para enviar archivos
$result = ftp_put($ftp, "/users/respaldos/home/bygmysql/".$backup_file, $backup_file, FTP_ASCII);
error_log("RESPALDO - FTP PUT RESULTADO: ".$result);

// cerrar conex ftp
ftp_close($ftp);

?>