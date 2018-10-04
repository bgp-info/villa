<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

#print_arr($cgi);

$sql = "select * from price_main"; 
$sth = my_exec($sql);
$arrInfo = array();
while ($row = mysql_fetch_array($sth)) {
	$arrInfo[$row[exhibition_id]][$row[service_id]][rub] = $row[price_rub];
	$arrInfo[$row[exhibition_id]][$row[service_id]][eur] = $row[price_eur];
}

$text_menu = $objAdmMenu->GetMenu($user_id, 'Финансы');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

show(); 

include_once("usr_templ.php");


#######################################################################





function show() {
  global $body, $table, $cgi, $arrUser, $arrInfo, $arrCompany, $lists, $cmp_desc, $kurs_euro, $this_project;
#  print_arr($arrUser);
/*
  $sql = "select dogovor_id from service group by dogovor_id";
  $sth = my_exec($sql);
  $arrServ = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrServ[$row[dogovor_id]] = 'Y';   
  }
*/
	$body = do_search_here();

  

  if ($cgi["search_client_id"]) { # Выбрали одну компанию...

    $sql = "select * from company where id = '".$cgi["search_client_id"]."'";
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
    $c_name = stripslashes($row[name]);
    $c_name_full = stripslashes($row[name_full]);

    $body .= "<center><p align=centre><b>Подробная информация по экспоненту ".$c_name."</b></p>";

    $where_main = " and dogovor.exhibition_id=".$this_project;

    $sql = "select dogovor.* from dogovor where dogovor.client_id = '".$cgi["search_client_id"]."' ".$where_main;
#    echo $sql;
    $sth = my_exec($sql);
    while ($row = mysql_fetch_array($sth)) {
 
      $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
      $body .= "<tr valign=top><td height=26 colspan=2 align=center> <a href='print_finans.php?id=".$row[id]."' target='_blank'>Версия для печати</a></td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Название экспонента</b></td><td>".$c_name_full."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Номер договора</b></td><td>".make_num($row[number])."&nbsp;</td></tr>\n";
   

      $sql = "select service.col, price.price_rub, price.price_eur from service, price where service.service_id = price.id and service.dogovor_id='".$row[id]."'";
      $sth_s = my_exec($sql);
      $sumServ = 0;
      while ($row_s = mysql_fetch_array($sth_s)) {
        if ($row[currency_id] == 2) { # !!!!!!! Беру это поле из договора (предыдущая выборка)
          $sumServ += $row_s[col] * $row_s[price_eur];
        } else {
          $sumServ += $row_s[col] * $row_s[price_rub];
        }    
      }

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
        $summ = number_format((($arrInfo[$row[exhibition_id]][1][eur] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][eur] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][eur] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][eur] * $s_st + $arrInfo[$row[exhibition_id]][5][eur] * $s_okt + $sumServ)),2,'.',''); # * 1.18
      } else { # RUB
        $summ = number_format((($arrInfo[$row[exhibition_id]][1][rub] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][rub] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][rub] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][rub] * $s_st + $arrInfo[$row[exhibition_id]][5][rub] * $s_okt + $sumServ)),2,'.',''); # * 1.18
      }
      $body .= "<tr valign=top><td height=26><b>Сумма договора</b></td><td>".$summ." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td></tr>\n";
/*
      $summ_serv_eur = $serv_eur + ($serv_rub / $kurs_euro);
      $summ_serv_rub = $serv_rub + ($serv_eur * $kurs_euro);

      if ($row['currency_id'] == 2) { # EURO
        $summ_eur = number_format((($arrInfo[1][eur] * $row['vznos'] + ($arrInfo[2][eur] * $s_pav * (1 + $row[charge_poz]) + $arrInfo[3][eur] * $s_otk) * $row[discount] + $arrInfo[4][eur] * $s_st + $arrInfo[5][eur] * $s_okt + $summ_serv_eur) * 1.18),2,'.','');
        $body .= "<tr valign=top><td height=26><b>Сумма договора</b></td><td>".$summ_eur." &euro;&nbsp;</td></tr>\n";
      } else { # RUB
        $summ_rub = number_format((($arrInfo[1][rub] * $row['vznos'] + ($arrInfo[2][rub] * $s_pav * (1 + $row[charge_poz]) + $arrInfo[3][rub] * $s_otk) * $row[discount] + $arrInfo[4][rub] * $s_st + $arrInfo[5][rub] * $s_okt + $summ_serv_rub) * 1.18),2,'.','');
        $body .= "<tr valign=top><td height=26><b>Сумма договора</b></td><td>".$summ_rub." руб.&nbsp;</td></tr>\n";
      }
*/
 #     $body .= "<tr valign=top><td height=26 colspan=2></td></tr>\n";

      $body .= "<tr valign=top><td colspan=2><b>Счета</b><br>";
      $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
      $body .= "<tr valign=top bgcolor=dddddd><td height=26><b>номер</b></td><td><b>дата</b></td><td><b>наименование</b></td><td><b>сумма</b></td><td><b>долг</b></td></tr>\n";
      				

      $sql_sch = "select * from schet where dogovor_id = '".$row[id]."' and status = 0 order by id desc";
      $sth_sch = my_exec($sql_sch);
      $arrS = array();
      while ($row_sch = mysql_fetch_array($sth_sch)) {
        $arrS[$row_sch[id]] = $row_sch;
      }
    
      if (count($arrS)) {
        $sql_sp = "select * from plateg_schet where schet_id in (".join(",",array_keys($arrS)).")";
        $sth_sp = my_exec($sql_sp);
        $arrSP = array();
        while ($row_sp = mysql_fetch_array($sth_sp)) {
          $arrSP[$row_sp[schet_id]] += $row_sp[summ];
        }
      }

      foreach($arrS as $k=>$row_sch) {
        $body .= "<tr valign=top><td height=26><b>".make_num($row_sch[number])."</b></td><td>".mysql2date2($row_sch[date])."&nbsp;</td><td>".nl2br(stripslashes($row_sch[note]))."&nbsp;</td>";
        $body .= "<td>".$row_sch[summ]." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td>";
        $body .= "<td>".($row_sch[summ] - $arrSP[$row_sch[id]])." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td></tr>\n";
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

    }


    $body .= "<form method=post>\n";
    $body .= "<input type=button onClick=window.location.href='/finans.php' value='  Вернуться к списку  '>\n";
    $body .= "</form></center>\n";

  } else {

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

    $sql = "select * from plateg";
    $sth = my_exec($sql);
    $arrPlateg = array();
    while ($row = mysql_fetch_array($sth)) {
      $arrPlateg[$row[dogovor_id]]  += $row[summ];
    }
#    print_arr($arrPlateg);


    $body .= "<br><br>";
    $body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr valign=top align=center class=tr1>";

  #	$body .= "<td><b>№ договора</b></td>";
    if($cgi[oldOrdCol] == 'number'){
      if($cgi[ordDesc] == ' desc '){
        $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
      }else{
        $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
      }
    } else {
      $str = "";
    }
    $body .= "<td rowspan=2><a href='#' onClick=\"document.form_search.ordCol.value='number'; document.form_search.submit();return false;\"><b><font color=white>№ договора</font></b></a>&nbsp;&nbsp;$str</td>";

  #	$body .= "<td><b>экспонент</b></td>";
    if($cgi[oldOrdCol] == 'client_id'){
      if($cgi[ordDesc] == ' desc '){
        $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
      }else{
        $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
      }
    } else {
      $str = "";
    }
    $body .= "<td rowspan=2><a href='#' onClick=\"document.form_search.ordCol.value='client_id'; document.form_search.submit();return false;\"><b><font color=white>экспонент</font></b></a>&nbsp;&nbsp;$str</td>";

#    $body .= "<td colspan=2><b>Сумма договора</b></td>";
    if($cgi[oldOrdCol] == 'summ'){
      if($cgi[ordDesc] == ' desc '){
        $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
      }else{
        $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
      }
    } else {
      $str = "";
    }
    $body .= "<td colspan=2><a href='#' onClick=\"document.form_search.ordCol.value='summ'; document.form_search.submit();return false;\"><b><font color=white>Сумма договора</font></b></a>&nbsp;&nbsp;$str</td>";

    $body .= "<td colspan=2><b>Оплачено</b></td>";

#    $body .= "<td colspan=2><b>Долг</b></td>";
    if($cgi[oldOrdCol] == 'dolg'){
      if($cgi[ordDesc] == ' desc '){
        $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
      }else{
        $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
      }
    } else {
      $str = "";
    }
    $body .= "<td colspan=2><a href='#' onClick=\"document.form_search.ordCol.value='dolg'; document.form_search.submit();return false;\"><b><font color=white>Долг</font></b></a>&nbsp;&nbsp;$str</td>";
    $body .= "</tr>";
    $body .= "<tr valign=top align=center class=tr1><td>руб.</td><td>€</td><td>руб.</td><td>€</td><td>руб.</td><td>€</td></tr>";


    if ($arrUser[1] == 'm') {
      $where_m = " and company.manager_id='".$arrUser[0]."'";
    }

  #  if ($cgi["search_client_id"]) {
  #    $arrWhere[] = "dogovor.client_id='".$cgi["search_client_id"]."'";
  #	}

    if($cgi[oldOrdCol]){
      if ($cgi[oldOrdCol] == 'number') {
        $order = " order by dogovor.number ".$cgi[ordDesc];
      }	elseif ($cgi[oldOrdCol] == 'client_id') {
        $order = " order by c_name ".$cgi[ordDesc];
      }
    } else {
      $order = " order by dogovor.number desc";
    }

    if (count($arrWhere)) {
      $where = "and ".join(" and ",$arrWhere);
    }
    if ($cgi["btn_all"]) {
      $where = "";
    }

    $where_main = " and dogovor.exhibition_id=".$this_project;

#    $sql = "select dogovor.*, stend.number as s_number, stend.friz as s_friz, company.name as c_name from dogovor left join company on company.id = dogovor.client_id left join stend on stend.dogovor_id = dogovor.id where dogovor.status_id=0 $where_m $where $order";

    $sql = "select dogovor.*, c.name as c_name from dogovor, company c where dogovor.status_id=0 and c.id=dogovor.client_id $where_main $where $order";
  #			echo $sql;
    $sth = my_exec($sql);
    $arrAll = array();
    while($row = mysql_fetch_array($sth)) {

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
/*
      $summ_serv_eur = number_format(($arrServ[$row[id]][eur] + ($arrServ[$row[id]][rub] / $kurs_euro)),2,'.','');
      $summ_serv_rub = number_format(($arrServ[$row[id]][rub] + ($arrServ[$row[id]][eur] * $kurs_euro)),2,'.','');

      if ($row['currency_id'] == 2) { # EURO
        $row[summ_eur] = number_format((($arrInfo[1][eur] * $row['vznos'] + ($arrInfo[2][eur] * $s_pav * (1 + $row[charge_poz]) + $arrInfo[3][eur] * $s_otk) * $row[discount] + $arrInfo[4][eur] * $s_st + $arrInfo[5][eur] * $s_okt + $summ_serv_eur) * 1.18),2,'.','');
      } else { # RUB
        $row[summ_rub] = number_format((($arrInfo[1][rub] * $row['vznos'] + ($arrInfo[2][rub] * $s_pav * (1 + $row[charge_poz]) + $arrInfo[3][rub] * $s_otk) * $row[discount] + $arrInfo[4][rub] * $s_st + $arrInfo[5][rub] * $s_okt + $summ_serv_rub) * 1.18),2,'.','');
      }
      $row[summ_sort] = $row[summ_rub] + $row[summ_eur] * $kurs_euro;

      $row[dolg_sort] = ($row[summ_rub] + $row[summ_eur] * $kurs_euro) - ($arrPlateg[$row[id]][rub] + $arrPlateg[$row[id]][eur] * $kurs_euro);
*/

      if ($row['currency_id'] == 2) { # EURO
        $row[summ] = number_format((($arrInfo[$row[exhibition_id]][1][eur] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][eur] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][eur] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][eur] * $s_st + $arrInfo[$row[exhibition_id]][5][eur] * $s_okt + $arrServ[$row[id]])),2,'.',''); # * 1.18
        $row[summ_sort] = $row[summ] * $kurs_euro;
        $row[dolg_sort] = ($row[summ] - $arrPlateg[$row[id]]) * $kurs_euro;
      } else { # RUB
        $row[summ] = number_format((($arrInfo[$row[exhibition_id]][1][rub] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][rub] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][rub] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][rub] * $s_st + $arrInfo[$row[exhibition_id]][5][rub] * $s_okt + $arrServ[$row[id]])),2,'.',''); # * 1.18
        $row[summ_sort] = $row[summ];
        $row[dolg_sort] = $row[summ] - $arrPlateg[$row[id]];
      }

 #     $row[dolg_sort] = ($row[summ_rub] + $row[summ_eur] * $kurs_euro) - ($arrPlateg[$row[id]][rub] + $arrPlateg[$row[id]][eur] * $kurs_euro);

      $arrAll[$row[id]] = $row;
    } 

    if($cgi[oldOrdCol] == 'summ'){
      $cmp_desc = $cgi[ordDesc];
      usort($arrAll, "cmp_summ");
    } elseif($cgi[oldOrdCol] == 'dolg'){
      $cmp_desc = $cgi[ordDesc];
      usort($arrAll, "cmp_dolg");
    }

    foreach ($arrAll as $k=>$row) {
      $dolg = number_format(($row[summ] - $arrPlateg[$row[id]]),2,'.','');

      $body .= "<tr valign=top>";
      $body .= "<td align=center><a href='/dogovor.php?page=info&num_str=".$row['id']."'>".make_num($row[number])."</a>&nbsp;</td>";
      $body .= "<td><a href='/finans.php?search_client_id=".$row['client_id']."'>".stripslashes($row[c_name])."</a>&nbsp;</td>";
      
      $body .= "<td>".($row['currency_id']==1?$row[summ]:"&nbsp;")."&nbsp;</td><td>".($row['currency_id']==2?$row[summ]:"&nbsp;")."&nbsp;</td>";
      $body .= "<td>".($row['currency_id']==1?$arrPlateg[$row[id]]:"&nbsp;")."&nbsp;</td><td>".($row['currency_id']==2?$arrPlateg[$row[id]]:"&nbsp;")."&nbsp;</td>";
      $body .= "<td>".($row['currency_id']==1?$dolg:"&nbsp;")."&nbsp;</td><td>".($row['currency_id']==2?$dolg:"&nbsp;")."&nbsp;</td>";
      
      $body .= "</tr>\n"; 

      if ($row['currency_id']==2) { # Euro
        $all_summ_eur += $row[summ];
        $all_plateg_eur += $arrPlateg[$row[id]];
        $all_dolg_eur += $dolg;
      } else {
        $all_summ_rub += $row[summ];
        $all_plateg_rub += $arrPlateg[$row[id]];
        $all_dolg_rub += $dolg;
      }
    }
    $body .= "<tr valign=top align=right><td colspan=2><b>Итого:&nbsp;&nbsp;</b></td>";
    $body .= "<td><b>$all_summ_rub</b>&nbsp;</td>";
    $body .= "<td><b>$all_summ_eur</b>&nbsp;</td>";
    $body .= "<td><b>$all_plateg_rub</b>&nbsp;</td>";
    $body .= "<td><b>$all_plateg_eur</b>&nbsp;</td>";
    $body .= "<td><b>$all_dolg_rub</b>&nbsp;</td>";
    $body .= "<td><b>$all_dolg_eur</b>&nbsp;</td>";
    $body .= "</tr>";

    $body .= "</table>\n";

  }

  return $body;
}

function do_search_here() {
	global $cgi, $table, $arrUser, $this_project;
  
  if ($arrUser[1] == 'm') {
    $sql = "select company.id, company.name as c_name, dogovor.number from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' and company.manager_id='".$arrUser[0]."' order by company.name, dogovor.number";
  } else {
    $sql = "select company.id, company.name as c_name, dogovor.number from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' order by company.name, dogovor.number";
  }
  $sth = my_exec($sql);
  $arrCompany = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrCompany[$row[id]] = stripslashes($row[c_name])." (договор № ".$row[number].")";
  }

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
    $cgi["search_client_id"] = "";
  }

  $cont .= "<tr><td><b>Экспонент</b>&nbsp;&nbsp;</td><td>".do_combobox('search_client_id',$arrCompany,$cgi["search_client_id"],'',1)."</td></tr>\n";

	$cont .= "</td></tr></table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
	$cont .= "<br><input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
	$cont .= "<input type=submit name=btn_search value='Поиск'>";
	$cont .= "</td></tr></table></form>\n";

	return $cont;
}

function cmp_summ($a, $b) {
  global $cmp_desc, $cgi;
#  echo $cgi[ordDesc]."<br>";
#  echo "'".$cmp_desc."'";
  if ($a[summ_sort] == $b[summ_sort]) {
    return 0;
  }
  if (trim($cmp_desc) == 'desc') {
    return ($a[summ_sort] > $b[summ_sort]) ? -1 : 1;
  } else {
    return ($a[summ_sort] < $b[summ_sort]) ? -1 : 1;
  }
}
function cmp_dolg($a, $b) {
  global $cmp_desc, $cgi;
#  echo $cgi[ordDesc]."<br>";
#  echo "'".$cmp_desc."'";
  if ($a[dolg_sort] == $b[dolg_sort]) {
    return 0;
  }
  if (trim($cmp_desc) == 'desc') {
    return ($a[dolg_sort] > $b[dolg_sort]) ? -1 : 1;
  } else {
    return ($a[dolg_sort] < $b[dolg_sort]) ? -1 : 1;
  }
}
?>