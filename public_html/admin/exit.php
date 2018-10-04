<?
include_once("../../private/func.inc");
setcookie("cookie_adm", "");
setcookie("cookie_adm_passwd", "");
header("Location:http://$HTTP_HOST/admin/index.php");
exit();
?>