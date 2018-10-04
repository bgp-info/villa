<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}
#print_arr($cgi);

$text_menu = $objAdmMenu->GetMenu($user_id, 'Отчеты');
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

$sql = "select id, name from exhibition order by id desc";
$sth = my_exec($sql);
$arrExhibition = array();
while ($row = mysql_fetch_array($sth)) {
  $arrExhibition[$row[id]] = $row[name];
}
$sql = "select id, name from work_type order by id desc";
$sth = my_exec($sql);
$arrWorkType = array();
while ($row = mysql_fetch_array($sth)) {
  $arrWorkType[$row[id]] = $row[name];
}

$arrDogInfo = array();
$sql = "select service.dogovor_id, service.id as s_id, service.col, dogovor.currency_id, price.* from service, price, dogovor where service.service_id = price.id and service.dogovor_id=dogovor.id order by s_id desc";
$sth = my_exec($sql);
while($row = mysql_fetch_array($sth)) {
  $arrDogInfo[$row[dogovor_id]][service][] = stripslashes($row[name]);
}
#print_arr($arrDogInfo);
$sql = "select dogovor_id, number2, date from schet order by id desc";
$sth = my_exec($sql);
while ($row = mysql_fetch_array($sth)) {
  $arrDogInfo[$row[dogovor_id]][schet][] = $row[number2]." от ".mysql2date2($row[date]);
}
$sql = "select dogovor_id, name, date from plateg order by id desc";
$sth = my_exec($sql);
while ($row = mysql_fetch_array($sth)) {
  $arrDogInfo[$row[dogovor_id]][plateg][] = $row[name]." от ".mysql2date2($row[date]);
}

$cgi[search_date_1_def]='2000-01-01';
$cgi[search_date_2_def]='2010-01-01';
if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

$cgi['vznos'] = ereg_replace(",",".",$cgi['vznos']);
$cgi['discount'] = ereg_replace(",",".",$cgi['discount']);
$cgi['area_all'] = ereg_replace(",",".",$cgi['area_all']);
$cgi['area_open'] = ereg_replace(",",".",$cgi['area_open']);

$cgi['exhibition_id'] = $this_project;

if(isset($cgi['add_row'])) {
  $sql = "select max(number) from $table where exhibition_id='".$cgi['exhibition_id']."'";
  $sth = my_exec($sql);
  $cgi[number] = mysql_result($sth, 0) + 1;
}

if(!isset($cgi['page'])) {$cgi['page']='make';}

switch($cgi['page']){
case 'make':
  make();
  break;
case 'show':
  show();
  break;
default:
  make();
}
include_once("usr_templ.php");


#######################################################################

function make() {
  global $body, $table, $cgi, $user_id, $arrUser, $lists, $arrInfo, $arrData, $cmp_desc, $this_project;
  $body = do_search_here();
}

function show() {
  global $body, $table, $cgi, $user_id, $arrUser, $lists, $arrInfo, $arrData, $cmp_desc, $this_project, $arrExhibition, $arrManager, $arrWorkType, $arrDogInfo;
  #print_arr($cgi);
/*
  $sql = "select service.dogovor_id, service.currency_id, service.col, info.price_rub, info.price_eur from service, info where service.service_id = info.id";
  $sth = my_exec($sql);
  $arrServ = array();
  while ($row = mysql_fetch_array($sth)) {
    if ($row[currency_id] == 2) {
      $arrServ[$row[dogovor_id]][eur] += $row[col] * $row[price_eur];
    } else {
      $arrServ[$row[dogovor_id]][rub] += $row[col] * $row[price_rub];
    }
  }
*/
/*
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

*/

  $body .= "<a href='$PHP_SELF?page=make'>Сформировать новый отчет</a><br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  $body .= "<td><b><font color=white>№</font></b></td>";
  if ($cgi['f_exh']) { $body .= "<td><b><font color=white>Проект</font></b></td>"; }
  if ($cgi['f_man']) { $body .= "<td><b><font color=white>Менеджер</font></b></td>"; }
  if ($cgi['f_dog']) { $body .= "<td><b><font color=white>Договор</font></b></td>"; }
  if ($cgi['f_cli']) { $body .= "<td><b><font color=white>Клиент</font></b></td>"; }
  if ($cgi['f_sta']) { $body .= "<td><b><font color=white>Статус</font></b></td>"; }
  if ($cgi['f_wt']) { $body .= "<td><b><font color=white>Вид деятельности</font></b></td>"; }
  if ($cgi['f_tr']) { $body .= "<td><b><font color=white>Текущие результаты</font></b></td>"; }
  if ($cgi['f_tz']) { $body .= "<td><b><font color=white>Текущие задачи</font></b></td>"; }
  if ($cgi['f_dop']) { $body .= "<td><b><font color=white>Доп. услуги о оборудование</font></b></td>"; }
  if ($cgi['f_sch']) { $body .= "<td><b><font color=white>Счета</font></b></td>"; }
  if ($cgi['f_pla']) { $body .= "<td><b><font color=white>Платежи</font></b></td>"; }
  if ($cgi['f_plo']) { $body .= "<td><b><font color=white>Площадь в павильоне</font></b></td>"; }
  if ($cgi['f_otk']) { $body .= "<td><b><font color=white>Открытая полщадь</font></b></td>"; }
  if ($cgi['f_std']) { $body .= "<td><b><font color=white>Стандартное оборудование</font></b></td>"; }

  #echo " 1 - ".$cgi['f_exh'];

/*
	$body .= "<td rowspan=2><b>рег. взнос, шт.</b></td>";
#	$body .= "<td><b>площадь в пав-не, кв.м</b></td>";
  if($cgi[oldOrdCol] == 'area_all'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td rowspan=2><a href='#' onClick=\"document.form_search.ordCol.value='area_all'; document.form_search.submit();return false;\"><b><font color=white>площадь в пав-не, кв.м</font></b></a>&nbsp;&nbsp;$str</td>";

#	$body .= "<td><b>откр. площадь, кв.м</b></td>";
  if($cgi[oldOrdCol] == 'area_open'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td rowspan=2><a href='#' onClick=\"document.form_search.ordCol.value='area_open'; document.form_search.submit();return false;\"><b><font color=white>откр. площадь, кв.м</font></b></a>&nbsp;&nbsp;$str</td>";

	$body .= "<td colspan=2><b>оборуд., кв.м</b></td>";
	$body .= "<td colspan=2><b>доп. услуги и оборуд.</b></td>";
	$body .= "<td rowspan=2><b>скидка, %</b></td>";
	$body .= "<td rowspan=2><b>наценка, %</b></td>";
#	$body .= "<td><b>сумма, руб./ &euro;</b></td>";
  if($cgi[oldOrdCol] == 'summ'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td colspan=2><a href='#' onClick=\"document.form_search.ordCol.value='summ'; document.form_search.submit();return false;\"><b><font color=white>сумма</font></b></a>&nbsp;&nbsp;$str</td>";
*/
  $body .= "</tr>";

/*
	if($cgi[oldOrdCol]){
		if ($cgi[oldOrdCol] == 'number') {
			$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
		}	elseif ($cgi[oldOrdCol] == 'date') {
			$order = " order by date ".$cgi[ordDesc];
		}	elseif ($cgi[oldOrdCol] == 'client_id') {
			$order = " order by c_name ".$cgi[ordDesc];
		}	elseif ($cgi[oldOrdCol] == 'area_all') {
			$order = " order by area_all ".$cgi[ordDesc];
		}	elseif ($cgi[oldOrdCol] == 'area_open') {
			$order = " order by area_open ".$cgi[ordDesc];
		} else {
			$order = "";
    }
  } else {
    $order = " order by $table.number desc";
  }
*/
  if ($cgi['search_exhibition']) {
		$where .= " and exhibition_id in (".join(",",$cgi['search_exhibition']).")";
	}
  if ($cgi['search_manager']) {
		$where .= " and c.manager_id in (".join(",",$cgi['search_manager']).")";
	}
  if ($cgi['search_work_type']) {
    $arrWWT = array();
    foreach($cgi['search_work_type'] as $k=>$v) {
      $arrWWT[] = "c.work_type like '%:".$v.":%'";
    }
    $where .= " and (".join(" or ", $arrWWT).")";
	}

	$sql = "select $table.*, c.name as c_name, c.manager_id, c.work_type, cp.status from company c, $table left join company_project cp on ($table.exhibition_id=cp.project_id and $table.client_id=cp.company_id) where c.id=$table.client_id $where_main $where $order";
		#	echo $sql;
	$sth = my_exec($sql);
  $arrAll = array();
  
	while($row = mysql_fetch_array($sth)) {
    #  договора = (P1*Q + (P2*Sпав*(1+EC) + P3*Sотк)*D + P4*Sст + P5*Sокт + ЕЕ)*1,18

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

 #   $summ_serv_eur = number_format(($arrServ[$row[id]][eur] + ($arrServ[$row[id]][rub] / $kurs_euro)),2,'.','');
 #   $summ_serv_rub = number_format(($arrServ[$row[id]][rub] + ($arrServ[$row[id]][eur] * $kurs_euro)),2,'.','');
    #if ($row[id] == 274) { echo $arrInfo[$row[exhibition_id]][1][rub]." - ".$row['vznos']; }
    if ($row['currency_id'] == 2) { # EURO
      $row[summ] = number_format((($arrInfo[$row[exhibition_id]][1][eur] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][eur] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][eur] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][eur] * $s_st + $arrInfo[$row[exhibition_id]][5][eur] * $s_okt + $arrServ[$row[id]])),2,'.',''); #  * 1.18
    } else { # RUB
      $row[summ] = number_format((($arrInfo[$row[exhibition_id]][1][rub] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][rub] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][rub] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][rub] * $s_st + $arrInfo[$row[exhibition_id]][5][rub] * $s_okt + $arrServ[$row[id]])),2,'.',''); #  * 1.18
    }
    $row[s_st] = $s_st;
    $row[s_okt] = $s_okt;
    if ($cgi['search_status'] && !in_array($row[status],$cgi['search_status'])) {
      continue;
    } else {
      $arrAll[$row[id]] = $row;
    }
  }
   #print_arr($arrAll);
/*
  if($cgi[oldOrdCol] == 'summ'){
#    echo $cgi[ordDesc];
    $cmp_desc = $cgi[ordDesc];
    usort($arrAll, "cmp");
  }
*/
  $i = 0;
  foreach ($arrAll as $k=>$row) { # Сперва все действующие
  #echo $row[status_id]." - ".$cgi['f_exh']."<br>";
    if ($row[status_id] == 0) {
      $s1 = $s2 = $bg = "";

      $body .= "<tr valign=top $bg>";
      $body .= "<td align=center>$s1".(++$i)."$s2&nbsp;</td>";
      if ($cgi['f_exh']) {
        $body .= "<td>$s1".$arrExhibition[$row[exhibition_id]]."$s2&nbsp;</td>";
      }
      if ($cgi['f_man']) {
        $body .= "<td>$s1".$arrManager[$row[manager_id]]."$s2&nbsp;</td>";
      }
      if ($cgi['f_dog']) {
        $body .= "<td>$s1".$row[number]." от ".mysql2date($row[date])."$s2&nbsp;</td>";
      }
      if ($cgi['f_cli']) {
        $body .= "<td>$s1".$row[c_name]."$s2&nbsp;</td>";
      }
      if ($cgi['f_sta']) {
        $body .= "<td>$s1".$lists[arrCompanyProjectStatus][$row[status]]."$s2&nbsp;</td>";
      }
      if ($cgi['f_wt']) {
        $arrWTName = array();
        $arrWT = split(":",substr($row[work_type],1,-1));
        foreach ($arrWT as $k=>$v) {
          $arrWTName[] = $arrWorkType[$v];
        }
        $body .= "<td>$s1".join(", ",$arrWTName)."$s2&nbsp;</td>";
      }
      if ($cgi['f_tr'] || $cgi['f_tz']) {
        $sql = "select cpp.* from company_project_plan cpp, company_project cp where cpp.company_project_id=cp.id and cp.company_id = ".$row[client_id]." and cp.project_id = ".$row[exhibition_id]." order by cpp.date desc limit 1";
        $sth_cpp = my_exec($sql);
        if (mysql_num_rows($sth_cpp)) {
          $row_cpp = mysql_fetch_array($sth_cpp);
          $tr = stripslashes($row_cpp[text]);
          $tz = stripslashes($row_cpp[task]);
        } else {
          $tr = $tz = "";
        }
        if ($cgi['f_tr']) {
          $body .= "<td>$s1".$tr."$s2&nbsp;</td>";
        }
        if ($cgi['f_tz']) {
          $body .= "<td>$s1".$tz."$s2&nbsp;</td>";
        }
      }
      if ($cgi['f_dop']) {
        $body .= "<td>$s1".($arrDogInfo[$row['id']]['service']?join("<br>",$arrDogInfo[$row['id']]['service']):"")."$s2&nbsp;</td>";
      }
      if ($cgi['f_sch']) {
        $body .= "<td>$s1".($arrDogInfo[$row['id']]['schet']?join("<br>",$arrDogInfo[$row['id']]['schet']):"")."$s2&nbsp;</td>";
      }
      if ($cgi['f_pla']) {
        $body .= "<td>$s1".($arrDogInfo[$row['id']]['plateg']?join("<br>",$arrDogInfo[$row['id']]['plateg']):"")."$s2&nbsp;</td>";
      }
      if ($cgi['f_plo']) {
        $body .= "<td>$s1".$row[area_all]."$s2&nbsp;</td>";
      }
      if ($cgi['f_otk']) {
        $body .= "<td>$s1".$row[area_open]."$s2&nbsp;</td>";
      }
      if ($cgi['f_std']) {
        $body .= "<td>$s1".$lists['arrEquipment'][$row[equipment_id]]."$s2&nbsp;</td>";
      }
      $body .= "</tr>\n";
/*
      $body .= "<td>$s1<a href='$PHP_SELF?page=upd&num_str=".$row['id']."'>".make_num($row[number])."</a>$s2</td><td>$s1".mysql2date2($row[date])."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1<a href=# onclick=\"return w_o('/company_info.php?id=".$row[client_id]."', 700,700, ',resizable=1,scrollbars=1')\">".stripslashes($row[c_name])."</a>$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[vznos]."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[area_all]."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[area_open]."$s2&nbsp;</td>";
      $body .= "<td align=center>$s1".$row[s_st]."$s2&nbsp;</td>";
      $body .= "<td align=center>$s1".$row[s_okt]."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==1?$arrServ[$row[id]]:"&nbsp;")."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==2?$arrServ[$row[id]]:"&nbsp;")."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[discount]."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[charge_poz]."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==1?$row[summ]:0)."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==2?$row[summ]:0)."$s2&nbsp;</td>";


      $all_vznos += $row[vznos];
      $all_area_all += $row[area_all];
      $all_area_open += $row[area_open];
      $all_area_st += $row[s_st];
      $all_area_okt += $row[s_okt];
      if ($row['currency_id'] == 2) { # EURO
        $all_summ[eur] += $row[summ];
        $all_service[eur] += $arrServ[$row[id]];
      } else {
        $all_summ[rub] += $row[summ];
        $all_service[rub] += $arrServ[$row[id]];
      }
      */
    }
	}

  foreach ($arrAll as $k=>$row) { # Затем не действующие
    if ($row[status_id] == 1) {
      $s1 = "<s>";
      $s2 = "</s>";
      $bg = "bgcolor=dddddd";

      $body .= "<tr valign=top $bg>";
      $body .= "<td align=center>$s1".(++$i)."$s2&nbsp;</td>";
      if ($cgi['f_exh']) {
        $body .= "<td>$s1".$arrExhibition[$row[exhibition_id]]."$s2&nbsp;</td>";
      }
      if ($cgi['f_man']) {
        $body .= "<td>$s1".$arrManager[$row[manager_id]]."$s2&nbsp;</td>";
      }
      if ($cgi['f_dog']) {
        $body .= "<td>$s1".$row[number]." от ".mysql2date($row[date])."$s2&nbsp;</td>";
      }
      if ($cgi['f_cli']) {
        $body .= "<td>$s1".$row[c_name]."$s2&nbsp;</td>";
      }
      if ($cgi['f_sta']) {
        $body .= "<td>$s1".$lists[arrCompanyProjectStatus][$row[status]]."$s2&nbsp;</td>";
      }
      if ($cgi['f_wt']) {
        $arrWTName = array();
        $arrWT = split(":",substr($row[work_type],1,-1));
        foreach ($arrWT as $k=>$v) {
          $arrWTName[] = $arrWorkType[$v];
        }
        $body .= "<td>$s1".join(", ",$arrWTName)."$s2&nbsp;</td>";
      }
      if ($cgi['f_tr'] || $cgi['f_tz']) {
        $sql = "select cpp.* from company_project_plan cpp, company_project cp where cpp.company_project_id=cp.id and cp.company_id = ".$row[client_id]." and cp.project_id = ".$row[exhibition_id]." order by cpp.date desc limit 1";
        $sth_cpp = my_exec($sql);
        if (mysql_num_rows($sth_cpp)) {
          $row_cpp = mysql_fetch_array($sth_cpp);
          $tr = stripslashes($row_cpp[text]);
          $tz = stripslashes($row_cpp[task]);
        } else {
          $tr = $tz = "";
        }
        if ($cgi['f_tr']) {
          $body .= "<td>$s1".$tr."$s2&nbsp;</td>";
        }
        if ($cgi['f_tz']) {
          $body .= "<td>$s1".$tz."$s2&nbsp;</td>";
        }
      }
      if ($cgi['f_dop']) {
        $body .= "<td>$s1".($arrDogInfo[$row['id']]['service']?join("<br>",$arrDogInfo[$row['id']]['service']):"")."$s2&nbsp;</td>";
      }
      if ($cgi['f_sch']) {
        $body .= "<td>$s1".($arrDogInfo[$row['id']]['schet']?join("<br>",$arrDogInfo[$row['id']]['schet']):"")."$s2&nbsp;</td>";
      }
      if ($cgi['f_pla']) {
        $body .= "<td>$s1".($arrDogInfo[$row['id']]['plateg']?join("<br>",$arrDogInfo[$row['id']]['plateg']):"")."$s2&nbsp;</td>";
      }
      if ($cgi['f_plo']) {
        $body .= "<td>$s1".$row[area_all]."$s2&nbsp;</td>";
      }
      if ($cgi['f_otk']) {
        $body .= "<td>$s1".$row[area_open]."$s2&nbsp;</td>";
      }
      if ($cgi['f_std']) {
        $body .= "<td>$s1".$lists['arrEquipment'][$row[equipment_id]]."$s2&nbsp;</td>";
      }
      $body .= "</tr>\n";
/*
      $body .= "<td>$s1<a href='$PHP_SELF?page=upd&num_str=".$row['id']."'>".make_num($row[number])."</a>$s2</td><td>$s1".mysql2date2($row[date])."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1<a href=# onclick=\"return w_o('/company_info.php?id=".$row[client_id]."', 700,700, ',resizable=1,scrollbars=1')\">".$row[c_name]."</a>$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[vznos]."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[area_all]."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[area_open]."$s2&nbsp;</td>";
      $body .= "<td align=center>$s1".$row[s_st]."$s2&nbsp;</td>";
      $body .= "<td align=center>$s1".$row[s_okt]."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==1?$arrServ[$row[id]]:"&nbsp;")."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==2?$arrServ[$row[id]]:"&nbsp;")."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[discount]."$s2&nbsp;</td>";
      $body .= "<td>$s1".$row[charge_poz]."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==1?$row[summ]:0)."$s2&nbsp;</td>";
      $body .= "<td>$s1".($row['currency_id']==2?$row[summ]:0)."$s2&nbsp;</td>";
      $body .= "</tr>\n";
      */
    }
  }
/*
  $body .= "<tr valign=top align=right><td colspan=4><b>Итого:&nbsp;&nbsp;</b></td>";
	$body .= "<td><b>$all_vznos</b>&nbsp;</td>";
	$body .= "<td><b>$all_area_all</b>&nbsp;</td>";
	$body .= "<td><b>$all_area_open</b>&nbsp;</td>";
	$body .= "<td><b>$all_area_st</b>&nbsp;</td>";
	$body .= "<td><b>$all_area_okt</b>&nbsp;</td>";
	$body .= "<td><b>".($all_service[rub]?$all_service[rub]." руб.":"&nbsp;")."</b></td>";
	$body .= "<td><b>".($all_service[eur]?$all_service[eur]." &euro;.":"&nbsp;")."</b></td>";
	$body .= "<td>&nbsp;</td>";
	$body .= "<td>&nbsp;</td>";
	$body .= "<td><b>".($all_summ[rub]?$all_summ[rub]." руб.":"&nbsp;")."</b></td>";
	$body .= "<td><b>".($all_summ[eur]?$all_summ[eur]." &euro;.":"&nbsp;")."</b></td>";
  $body .= "</tr>";
  */
	$body .= "</table>\n";

  return $body;
}

function do_search_here() {
	global $cgi, $table, $lists, $arrManager, $arrExhibition, $arrWorkType, $arrWorkType;
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

#  if ($cgi['btn_all']) {
#    $cgi[search_manager] = array();
#    $cgi[search_exhibition] = array();
#  }
	$cont = "<form name=form_search method=post>\n<table border=1 cellspacing=0 cellpadding=10 bordercolorlight=black bordercolordark=white align=center>\n";
	$cont .= "<tr><td align=center>";
	$cont .= "<table border=0>";

  $cont .= "<tr valign=top>";
  $cont .= "<td><b>Проект</b><br>".do_combobox("search_exhibition[]",$arrExhibition,'',' size=5 multiple style="width:100px"','')."</td>";
  $cont .= "<td><b>Менеджер</b><br>".do_combobox("search_manager[]",$arrManager,'',' size=5 multiple style="width:250px"','')."</td>";
  $cont .= "<td><b>Статус</b><br>".do_combobox("search_status[]",$lists[arrCompanyProjectStatus],'',' size=5 multiple style="width:100px"','')."</td>";
  $cont .= "<td><b>Вид деятельности</b><br>".do_combobox("search_work_type[]",$arrWorkType,'',' size=5 multiple style="width:250px"','')."</td>";
  $cont .= "</tr>\n";
  $cont .= "<tr><td colspan=4>* Вы можете выбрать несколько позиций, удерживая клавишу Ctrl</td></tr>";
  $cont .= "<tr><td colspan=4 align=center><b>Поля на вывод</b></td></tr>";
  $cont .= "<tr><td colspan=4>";
  $cont .= "<input type='checkbox' name=f_exh value='Y' checked> Проект&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_man value='Y' checked> Менеджер&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_dog value='Y' checked> Договора&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_cli value='Y' checked> Клиенты&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_sta value='Y' checked> Статус&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_wt value='Y' checked> Вид деятельности&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_tr value='Y' checked> Текущие результаты&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_tz value='Y' checked> Текущие задачи<br>";
  $cont .= "<input type='checkbox' name=f_dop value='Y' checked> Доп. услуги о оборудование&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_sch value='Y' checked> Счета&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_pla value='Y' checked> Платежи&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_plo value='Y' checked> Площадь в павильоне&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_otk value='Y' checked> Открытая полщадь&nbsp;&nbsp;&nbsp;";
  $cont .= "<input type='checkbox' name=f_std value='Y' checked> Стандартное оборудование&nbsp;&nbsp;&nbsp;";
  $cont .= "</td></tr>";
	$cont .= "</table><br>\n";
	$cont .= "<input type=hidden name=page value='show'>";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
#	$cont .= "<br><input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
	$cont .= "<input type=submit name=btn_search value='Сформировать отчет'>";
	$cont .= "</td></tr></table></form>\n";

	return $cont;
}

function cmp($a, $b) {
  global $cmp_desc, $cgi;
#  echo $cgi[ordDesc]."<br>";
#  echo "'".$cmp_desc."'";
  if ($a[summ] == $b[summ]) {
    return 0;
  }
  if (trim($cmp_desc) == 'desc') {
    return ($a[summ] > $b[summ]) ? -1 : 1;
  } else {
    return ($a[summ] < $b[summ]) ? -1 : 1;
  }
}
?>