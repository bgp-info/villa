<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}



$text_menu = $objAdmMenu->GetMenu($user_id, 'Финансы');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$sql = "select * from dogovor where id='".$cgi['id']."'";
$sth = my_exec($sql); 
$dogInfo = mysql_fetch_array($sth);

$sql = "select * from price_main where exhibition_id='".$dogInfo[exhibition_id]."'";
$sth = my_exec($sql);
$arrInfo = array();
while ($row = mysql_fetch_array($sth)) {
	$arrInfo[$row[service_id]][rub] = $row[price_rub];
	$arrInfo[$row[service_id]][eur] = $row[price_eur];
}

#print_arr($arrInfo);

$sql = "select service.col, price.price_rub, price.price_eur from service, price where service.service_id = price.id and service.dogovor_id='".$dogInfo[id]."'";
$sth = my_exec($sql);
$sumServ = 0;
while ($row = mysql_fetch_array($sth)) {
  if ($dogInfo[currency_id] == 2) {
    $sumServ += $row[col] * $row[price_eur];
  } else {
    $sumServ += $row[col] * $row[price_rub];
  }    
}

info($cgi['id']); 

include_once("empty_templ.php");


#######################################################################




function info($num_str) {
  global $body, $table, $cgi, $arrUser, $arrInfo, $arrCompany, $lists, $cmp_desc, $sumServ;

  $sql = "select dogovor.*, company.name as c_name, company.name_full as c_name_full from dogovor, company where dogovor.client_id = company.id and dogovor.id = '".$num_str."'";
#    echo $sql;
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);

  $body .= "<center><p align=centre><b>Подробная информация по экспоненту ".stripslashes($row[c_name])."</b></p>";

  $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top><td height=26><b>Название экспонента</b></td><td>".stripslashes($row[c_name_full])."&nbsp;</td></tr>\n";
  $body .= "<tr valign=top><td height=26><b>Номер договора</b></td><td>".make_num($row[number])."&nbsp;</td></tr>\n";


  $s_otk = $row[area_open];
  $s_pav = $row[area_all];
  if ($row[equipment_id] == 2) { # Стандарт
    $s_st = $row[area_all];
    $s_okt = 0;
  } elseif ($row[equipment_id] == 3) { # Октонорм
    $s_st = 0;
    $s_okt = $row[area_all];
  } else { # Нет
    $s_st = 0;
    $s_okt = 0;
  }

  if ($row['currency_id'] == 2) { # EURO
    $summ = number_format((($arrInfo[1][eur] * $row['vznos'] + ($arrInfo[2][eur] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[3][eur] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[4][eur] * $s_st + $arrInfo[5][eur] * $s_okt + $sumServ)),2,'.',''); # * 1.18
  } else { # RUB
    $summ = number_format((($arrInfo[1][rub] * $row['vznos'] + ($arrInfo[2][rub] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[3][rub] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[4][rub] * $s_st + $arrInfo[5][rub] * $s_okt + $sumServ)),2,'.',''); # * 1.18
  }
  $body .= "<tr valign=top><td height=26><b>Сумма договора</b></td><td>".$summ." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td></tr>\n";

#     $body .= "<tr valign=top><td height=26 colspan=2></td></tr>\n";
  $body .= "<tr valign=top><td colspan=2><b>Счета</b><br>";
  $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top bgcolor=dddddd><td height=26><b>номер</b></td><td><b>дата</b></td><td><b>наименование</b></td><td><b>сумма</b></td><td><b>оплата (да/нет/частично)</b></td></tr>\n";
          
  $sql_sch = "select * from schet where dogovor_id = '".$row[id]."' order by id desc";
  $sth_sch = my_exec($sql_sch);
  while ($row_sch = mysql_fetch_array($sth_sch)) {
    $body .= "<tr valign=top><td height=26><b>".make_num($row_sch[number])."</b></td><td>".mysql2date2($row_sch[date])."&nbsp;</td><td>".nl2br(stripslashes($row_sch[note]))."&nbsp;</td>";
    $body .= "<td>".$row_sch[summ]." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td>";
    $body .= "<td>&nbsp;</td></tr>\n";
  }
  $body .= "</table>\n";
  $body .= "</td></tr>\n";


#      $body .= "<tr valign=top><td height=26 colspan=2></td></tr>\n";
  $body .= "<tr valign=top><td colspan=2><b>Платежи</b><br>";
  $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top bgcolor=dddddd><td height=26><b>номер п/п</b></td><td><b>дата</b></td><td><b>сумма</b></td></tr>\n";

  $sql_p = "select * from plateg where dogovor_id = '".$row[id]."'";
  $sth_p = my_exec($sql_p);
  while ($row_p = mysql_fetch_array($sth_p)) {

    $body .= "<tr valign=top><td height=26><b>".$row_p[name]."</b></td><td>".mysql2date2($row_p[date])."&nbsp;</td>";
    $body .= "<td>".$row_p[summ]." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td>";
    $body .= "</tr>\n";

    $sum_plateg += $row_p[summ];
  }

  $dolg = number_format(($summ - $sum_plateg),2,'.','');

  $body .= "</table>\n";
  $body .= "</td></tr>\n";
  $body .= "<tr valign=top><td height=26><b>Задолженность</b></td><td>".$dolg." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td></tr>\n";

  $body .= "</table><BR>\n";


  $body .= "<form method=post>\n";
  $body .= "<INPUT onclick=window.print() type=button value=' Печатать ' class=show_btn>&nbsp;&nbsp;&nbsp;";
  $body .= "<input type=button onClick=window.close() value='  Закрыть окно  ' class=show_btn>\n";
  $body .= "</form></center>\n";

  return $body;
}

?>