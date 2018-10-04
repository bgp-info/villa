<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

#print_arr($cgi);

$sql = "select id, name from manager where type='m' order by name";
$sth = my_exec($sql);
$arrManager = array();
while ($row = mysql_fetch_array($sth)) {
	$arrManager[$row[id]] = $row[name];
}

$text_menu = $objAdmMenu->GetMenu($user_id, 'Статистика');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "statistic";

#######################################################################

#  print_arr($arrUser);

#	$body = do_search_here();
if (isset($cgi[id])) {
	$sql = "select statistic.*, manager.name as m_name from statistic, manager where manager.id = statistic.user_id and statistic.id='".$cgi[id]."'";
	$sth = my_exec($sql);
	$row = mysql_fetch_array($sth);

	$body = "<center><h4>Информация по операции</h4>";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	$body .= "<tr valign=top height=33><td><b>Дата</b></td><td>".mysql2datetime($row[date])."&nbsp;</td></tr>\n";
  $body .= "<tr valign=top height=33><td><b>Менеджер</b></td><td>".$row[m_name]."&nbsp;</td></tr>\n";
	$body .= "<tr valign=top height=33><td><b>Операция</b></td><td>".$row[comment]."&nbsp;</td></tr>\n";
	$body .= "<tr valign=top><td><b>Данные до операции</b></td><td>".stripslashes($row[text_old])."&nbsp;</td></tr>\n";
	$body .= "<tr valign=top><td><b>Данные после операции</b></td><td>".stripslashes($row[text_new])."&nbsp;</td></tr>\n";
	$body .= "</table>\n";
	$body .= "<br><br></center>\n";
} else {
  $body .= "<br><br>";

	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
	$body .= "<td><b>Дата</b></td>";
	$body .= "<td><b>Пользователь</b></td>";
	$body .= "<td><b>Операция</b></td>";
	$body .= "<td><b>Подробности</b></td>";
  $body .= "</tr>";


	$sql = "select statistic.*, manager.name as m_name from statistic, manager where manager.id = statistic.user_id order by statistic.date desc";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top $bg>";
    $body .= "<td>".mysql2datetime($row[date])."&nbsp;</td>\n"; 
    $body .= "<td>".$row[m_name]."&nbsp;</td>\n"; 
    $body .= "<td>".$row[comment]."&nbsp;</td>";
    $body .= "<td align=center><a href='/statistic.php?id=".$row[id]."'>Подробности</a></td>";
	}
  $body .= "</tr>";
	$body .= "</table>\n";
}

include_once("usr_templ.php");


function do_search_here() {
	global $cgi, $table, $arrUser, $lists;
  if($cgi[ordCol]){
    if($cgi[ordCol] != $cgi[oldOrdCol]){
      $cgi[ordDesc] = ' asc ';
    }else{
      if($cgi[ordDesc] == ' desc '){
        $cgi[ordDesc] = ' asc ';      
      }else{
        $cgi[ordDesc] = ' desc '; 
      }
    }
    $cgi[oldOrdCol] = $cgi[ordCol];
    $cgi[ordCol] = '';
  }
	$cont = "<form name=form_search method=post>\n<table border=1 cellspacing=0 cellpadding=10 bordercolorlight=black bordercolordark=white align=center>\n";
	$cont .= "<tr><td align=center>";
	$cont .= "<table border=0>";
	if ($cgi['btn_all']) {
    $cgi["search_status"] = "";
  }

  $cont .= "<tr><td><b>Статус</b>&nbsp;&nbsp;</td><td>".do_combobox('search_status',$lists[arrTaskStatus],$cgi["search_status"],'','')."</td>";
  $cont .= "<td>&nbsp;&nbsp;<input type=submit name=btn_search value='Поиск'></td>";
  
  $cont .= "</tr>\n";

	$cont .= "</table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
#	$cont .= "<input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
#	$cont .= "<input type=submit name=btn_search value='Поиск'>";
	$cont .= "</td></tr></table></form>\n";

	return $cont;
}


?>