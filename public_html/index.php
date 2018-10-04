<?php
/* adm_schr.php */

include_once("usr_menu.inc");
include_once("../private/func.inc");

if ($user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	$text_menu = $objAdmMenu->GetMenu($user_id, 'Вход');
	$arrUser = GetUserName($user_id);
	$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";
  $default = make_filter($user_id); 

	$body = "<br><br><center>Вы авторизованы!<br>Выберите интересующий Вас инструмент<br><br><br><br><br><br>\n";
} else {
	if (authenticateUser($cgi[usr_login], $cgi[usr_passw])) {
		setcookie("cookie_login", $cgi[usr_login]);
		setcookie("cookie_passwd", $cgi[usr_passw]);
		header("Location:/index.php");
		exit();
	} else {
		$text_menu = "";
		$manager = "<font color=red>Вам необходимо авторизоваться...</font>";
		$body = "<br><p><center>Для начала работы в системе,<br> Вам необходимо ввести выданный администратором логин (email) и пароль.<br><br>";
		$body .= "<form method=post>\n";
		$body .= "<input type=hidden name=num_str value=$num_str>\n";
		$body .= "<table class=small alert=center border=0 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
		$body .= "<tr><td align=right><b>Логин:</b>&nbsp;&nbsp;</td><td><input name=usr_login value='' size=25></td></tr>\n"; 
		$body .= "<tr><td align=right><b>Пароль:</b>&nbsp;&nbsp;</td><td><input type=password name=usr_passw value='' size=25></td></tr>\n"; 
		$body .= "</table><BR>\n";
		$body .= "<input type=submit name=submit value='Войти в систему'>\n";
		$body .= "</form></center>\n";
		$body .= "<br><br><br><br><br><br>\n";
	}
}

include_once("usr_templ.php");


?>