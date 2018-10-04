<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

#print_arr($cgi);

$text_menu = $objAdmMenu->GetMenu($user_id, 'Доп. услуги и оборудование');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "service";

switch($cgi['page']){
  case 'main':
    show();  
    break;
  case 'info':
    info($cgi['num_str']);      
    break;
  default:
    show(); 
}

include_once("usr_templ.php");

#######################################################################


function show() {
  global $body, $table, $cgi, $arrUser, $lists, $this_project;


  $body .= "<br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  /*
  if($cgi[oldOrdCol] == 'type_id'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='type_id'; document.form_search.submit();return false;\"><b><font color=white>Раздел</font></b></a>&nbsp;&nbsp;$str</td>";
*/
  $body .= "<td><b>Раздел</b></td>";
	$body .= "<td><b>Название</b></td>";
	$body .= "<td><b>Ед. изм.</b></td>";
	$body .= "<td><b>Цена, руб.</b></td>";
	$body .= "<td><b>Цена, €</b></td>";
	$body .= "<td><b>Количество</b></td>";
	$body .= "<td><b>Сумма, руб.</b></td>";
	$body .= "<td><b>Сумма, €</b></td>";
  $body .= "</tr>";

  $where_m = " and dogovor.exhibition_id=".$this_project;
  if ($arrUser[1] == 'm') {
    $where_m .= " and company.manager_id='".$arrUser[0]."'";
  }
  
	if($cgi[search_type_id]){
    $arrWhere[] = " price.type_id = '".$cgi[search_type_id]."'";
  }

	if($cgi[oldOrdCol]){
		$order = " order by r_name ".$cgi[ordDesc];
  } else {
    $order = " order by r_name, name";
  }

  $sql = "select dogovor.currency_id, service.service_id, service.col, price.name, price.ed_izm, price.price_rub, price.price_eur, razdel_price.name as r_name from dogovor, service, price, razdel_price, company where razdel_price.id = price.type_id and service.service_id = price.id and service.dogovor_id = dogovor.id and company.id = dogovor.client_id and dogovor.status_id=0 $where_m $where $order";
#  echo $sql;

  $arrServ = array();
  $sth = my_exec($sql);
  while($row = mysql_fetch_array($sth)) {
    $arrServ[$row[service_id]][r_name] = $row[r_name];
    $arrServ[$row[service_id]][name] = $row[name];
    $arrServ[$row[service_id]][ed_izm] = $row[ed_izm];
    $arrServ[$row[service_id]][price_rub] = $row[price_rub];
    $arrServ[$row[service_id]][price_eur] = $row[price_eur];
    $arrServ[$row[service_id]][all_col] += $row[col];
    if ($row[currency_id] == 2) { # !!!!!!! Беру это поле из договора (предыдущая выборка)
      $arrServ[$row[service_id]][summ_eur] += $row[col] * $row[price_eur];
    } else {
      $arrServ[$row[service_id]][summ_rub] += $row[col] * $row[price_rub];
    }
  }

#  print_arr($arrServ);

  foreach($arrServ as $k => $row) {
		$body .= "<tr valign=top>";

    $body .= "<td>".$row[r_name]."</a>&nbsp;</td>";
    $body .= "<td><a href='$PHP_SELF?page=info&num_str=".$k."'>".$row[name]."</a>&nbsp;</td>";
    $body .= "<td>".$lists['arrEdIzm'][$row[ed_izm]]."&nbsp;</td>";
    $body .= "<td>".$row[price_rub]."&nbsp;</td>";
    $body .= "<td>".$row[price_eur]."&nbsp;</td>";
    $body .= "<td>".$row[all_col]."&nbsp;</td>";
    $body .= "<td>".($row[summ_rub])."&nbsp;</td>";
    $body .= "<td>".($row[summ_eur])."&nbsp;</td>";
    $body .= "</tr>\n"; 
  }



  return $body;
}



function do_search_here($filds) {
	global $cgi, $table;
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
    $cgi[search_type_id] = '';
  }
  $sql = "select id, name from razdel_price order by name";
  $sth = my_exec($sql);
  while($row = mysql_fetch_array($sth)) {
    $arr[$row['id']] = stripslashes($row[name]);
  }
  $cont .= "<tr><td><b>Раздел</b>&nbsp;&nbsp;</td><td>".do_combobox("search_type_id",$arr,$cgi[search_type_id],'',1)."</td></tr>\n";

	$cont .= "</table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
	$cont .= "<br><input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
	$cont .= "<input type=submit name=btn_search value='Поиск'>";
	$cont .= "</td></tr></table></form>\n";

	return $cont;
}

function info($num_str) {
  global $body, $table, $cgi, $lists, $user_id, $this_project;

  $sql = "select name from price where id = $num_str";
  $sth = my_exec($sql);
  $header = "Информация по позиции ".mysql_result($sth,0);

  $where_m = " and dogovor.exhibition_id=".$this_project;
  if ($arrUser[1] == 'm') {
    $where_m .= " and company.manager_id='".$arrUser[0]."'";
  }

  $sql = "select dogovor.number, company.name as c_name, service.col, price.ed_izm from dogovor, service, price, company where service.service_id = price.id and service.dogovor_id = dogovor.id and company.id = dogovor.client_id and dogovor.status_id=0 and service.service_id = ".$num_str." $where_m $where ";
  #echo $sql;

  $sth = my_exec($sql);


	$body = "<center><h4>$header</h4>";
  $body .= "<form name=form_1 method=post>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top bgcolor=e0e0e0><td><b>№ договора</b></td><td><b>экспонент</b></td><td><b>ед. изм.</b></td><td><b>количество</b></td></tr>\n";

  $summ = 0;
  while($row = mysql_fetch_array($sth)) {
    $body .= "<tr valign=top><td>".$row[number]."</td><td>".$row[c_name]."</td><td>".$lists['arrEdIzm'][$row[ed_izm]]."</td><td>".$row[col]."</td></tr>\n";
    $summ += $row[col];
  }
  $body .= "<tr valign=top><td colspan=3 align=right><b>Итого:</b>&nbsp;</td><td>".$summ."</td></tr>\n";
/*

	$body .= "<tr valign=top><td><b>".$v[0]."</b></td><td>".$arrExhibition[$$k]."&nbsp;</td></tr>\n";
    } elseif ($k=='client_id') {
			$body .= "<tr valign=top><td><b>".$v[0]."</b></td><td>".$arrCompany[$$k]."&nbsp;</td></tr>\n";
		} elseif ($k=='number') {
			$body .= "<tr valign=top height=33><td><b>".$v[0]."</b></td><td><b>".make_num($$k)."&nbsp;</b></td></tr>\n";
    } elseif ($k=='manager_id') {
			$body .= "<tr valign=top><td><b>".$v[0]."</b></td><td>".$arrManager[$$k]."&nbsp;</td></tr>\n";
		} elseif ($v[1]=='REF') {
			$body .= "<tr valign=top><td><b>$v[0]</b></td><td>".$lists[$v[2]][$$k]."&nbsp;</td></tr>\n";
		} else {
			$body .= "<tr valign=top><td><b>$v[0]</b></td><td>".$$k."&nbsp;</td></tr>\n";
		}
	}


  while($row = mysql_fetch_array($sth)) {
    $arrServ[$row[service_id]][r_name] = $row[r_name];
    $arrServ[$row[service_id]][name] = $row[name];
    $arrServ[$row[service_id]][ed_izm] = $row[ed_izm];
    $arrServ[$row[service_id]][price_rub] = $row[price_rub];
    $arrServ[$row[service_id]][price_eur] = $row[price_eur];
    $arrServ[$row[service_id]][all_col] += $row[col];
    if ($row[currency_id] == 2) { # !!!!!!! Беру это поле из договора (предыдущая выборка)
      $arrServ[$row[service_id]][summ_eur] += $row[col] * $row[price_eur];
    } else {
      $arrServ[$row[service_id]][summ_rub] += $row[col] * $row[price_rub];
    }
  }

*/
	$body .= "</table><BR>\n";
	$body .= "<input type=button onClick='history.back();' value='  Вернуться  '><br>\n";
  $body .= "</form></center>\n";
  return $body;
}
?>