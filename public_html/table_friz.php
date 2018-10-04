<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}
#print_arr($cgi);


$text_menu = $objAdmMenu->GetMenu($user_id, 'Фризы');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);
#echo $this_project;

$table = "dogovor";

$sql = "select * from price_main";
$sth = my_exec($sql);
$arrInfo = array();
while ($row = mysql_fetch_array($sth)) {
	$arrInfo[$row[exhibition_id]][$row[service_id]][rub] = $row[price_rub];
	$arrInfo[$row[exhibition_id]][$row[service_id]][eur] = $row[price_eur];
}

$sql = "select id, name from manager where type='m' order by name";
$sth = my_exec($sql);
$arrManager = array();
while ($row = mysql_fetch_array($sth)) {
	$arrManager[$row[id]] = $row[name];
}

$sql = "select * from stend";
$sth = my_exec($sql);
$arrStend = array();
while ($row = mysql_fetch_array($sth)) {
	$arrStend[$row[dogovor_id]][number] = stripslashes($row[number]);
	$arrStend[$row[dogovor_id]][friz] = stripslashes($row[friz]);
}

$cgi[search_date_1_def]='2000-01-01';
$cgi[search_date_2_def]='2010-01-01';
if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

$cgi['exhibition_id'] = $this_project;

show();

include_once("usr_templ.php");


#######################################################################


function show() {
  global $body, $table, $cgi, $user_id, $arrUser, $lists, $arrInfo, $arrStend, $cmp_desc, $this_project;

  $sql = "select service.dogovor_id, service.col, price.price_rub, price.price_eur, dogovor.currency_id from service, price, dogovor where service.dogovor_id = dogovor.id and service.service_id = price.id";
  $sth = my_exec($sql);
  $arrServ = array();
  while ($row = mysql_fetch_array($sth)) {
    if ($row[currency_id] == 2) {
      $arrServ[$row[dogovor_id]] += $row[col] * $row[price_eur];
    } else {
      $arrServ[$row[dogovor_id]] += $row[col] * $row[price_rub];
    }
  }

#  print_arr($arrServ);

  $body = do_search_here();
  $body .= "<br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1><td width=30>№</td>";
#	$body .= "<td><b>№</b></td>";
  if($cgi[oldOrdCol] == 'number'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='number'; document.form_search.submit();return false;\"><b><font color=white>Договор</font></b></a>&nbsp;&nbsp;$str</td>";

#	$body .= "<td><b>Дата</b></td>";
  if($cgi[oldOrdCol] == 'stend'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='stend'; document.form_search.submit();return false;\"><b><font color=white>Стенд</font></b></a>&nbsp;&nbsp;$str</td>";

#	$body .= "<td><b>Экспонент</b></td>";
  if($cgi[oldOrdCol] == 'c_name'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='c_name'; document.form_search.submit();return false;\"><b><font color=white>Экспонент</font></b></a>&nbsp;&nbsp;$str</td>";

	$body .= "<td><b>Надпись на фризе</b></td>";

  $body .= "</tr>";

  if ($cgi['search_manager_id']) {
		$where .= " and c.manager_id=".$cgi['search_manager_id'];
	}

  if ($cgi["btn_all"]) {
		$where = "";
	}

  $where_main = " and $table.exhibition_id=".$this_project;
  $where_main .= " and $table.equipment_id in (2,3)";

	$sql = "select $table.*, c.name as c_name from $table, company c where c.id=$table.client_id $where_main $where";
	#		echo $sql;
	$sth = my_exec($sql);
  $arrAll = array();
	while($row = mysql_fetch_array($sth)) {
    $row[stend] = $arrStend[$row['id']][number];
    $row[friz] = $arrStend[$row['id']][friz];
    $arrAll[$row[id]] = $row;
  }
  #print_arr($arrAll);


  #print_arr($cgi);

  if($cgi[oldOrdCol]){
    masort($arrAll, $cgi[oldOrdCol].(trim($cgi[ordDesc])=='desc'?" ".trim($cgi[ordDesc]):""));
  } else {
    masort($arrAll, "number");
  }

  #print_arr($arrAll);

  $i = 0;
  foreach ($arrAll as $k=>$row) { # Сперва все действующие
    if ($row[status_id] == 0) {
      $s1 = $s2 = $bg = "";

      $body .= "<tr valign=top align=center $bg><td nowrap>".(++$i)."</td>";

      $body .= "<td>$s1<a href='/dogovor.php?page=info&num_str=".$row['id']."' target='_blank'>".make_num($row[number])."</a>$s2</td>";
      $body .= "<td>$s1".$row[stend]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1<a href=# onclick=\"return w_o('/company_info.php?id=".$row[client_id]."', 700,700, ',resizable=1,scrollbars=1')\">".stripslashes($row[c_name])."</a>$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[friz]."$s2&nbsp;</td>";
      $body .= "</tr>\n";
    }
	}

  foreach ($arrAll as $k=>$row) { # Затем не действующие
    if ($row[status_id] == 1) {
      $s1 = "<s>";
      $s2 = "</s>";
      $bg = "bgcolor=dddddd";

      $body .= "<tr valign=top align=center $bg><td nowrap>".(++$i)."</td>";

      $body .= "<td>$s1<a href='/dogovor.php?page=info&num_str=".$row['id']."' target='_blank'>".make_num($row[number])."</a>$s2</td>";
      $body .= "<td>$s1".$row[stend]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1<a href=# onclick=\"return w_o('/company_info.php?id=".$row[client_id]."', 700,700, ',resizable=1,scrollbars=1')\">".$row[c_name]."</a>$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[friz]."$s2&nbsp;</td>";
      $body .= "</tr>\n";
    }
  }


	$body .= "</table>\n";
  return $body;
}

function do_search_here() {
	global $cgi, $table, $arrManager;
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
    $cgi[search_manager_id] = '';
  }
  $cont .= "<tr><td><b>Менеджер</b>&nbsp;&nbsp;</td><td>".do_combobox("search_manager_id",$arrManager,$cgi[search_manager_id],'',1)."</td></tr>\n";

	$cont .= "</table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
	$cont .= "<br><input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
	$cont .= "<input type=submit name=btn_search value='Поиск'>";
	$cont .= "</td></tr></table></form>\n";

	return $cont;
}


?>