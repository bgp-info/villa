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

$table = "task";

show(); 

include_once("usr_templ.php");


#######################################################################


function show() {
  global $body, $table, $cgi, $arrUser, $lists;
#  print_arr($arrUser);

#	$body = do_search_here();

  $body .= "<br><br>";

	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
	$body .= "<td><b>Дата</b></td>";
	$body .= "<td><b>Пользователь</b></td>";
	$body .= "<td><b>Операция</b></td>";
	$body .= "<td><b>Старое значение</b></td>";
	$body .= "<td><b>Новое значение</b></td>";
  $body .= "</tr>";


	$sql = "select task_stat.*, task.text, manager.name as m_name from task_stat, task, manager where manager.id = task.manager_id and task_stat.task_id = task.id order by task_stat.datetime";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top $bg>";
    $body .= "<td>".mysql2datetime($row[datetime])."&nbsp;</td>\n"; 
    $body .= "<td>".$row[m_name]."&nbsp;</td>\n"; 
    $body .= "<td>".nl2br(stripslashes($row[text]))."&nbsp;</td>";
    $body .= "<td align=center>".$lists[arrTaskStatus][$row[status_old]]."</td>";
    $body .= "<td align=center>".$lists[arrTaskStatus][$row[status_new]]."</td>";

	}
  $body .= "</tr>";
	$body .= "</table>\n";

  return $body;
}

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