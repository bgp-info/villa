<?
include_once("../private/func.inc");
setcookie("cookie_login", "");
setcookie("cookie_passwd", "");
header("Location:/index.php");
exit();
?>