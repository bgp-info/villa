<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}
#print_arr($cgi);


if(isset($cgi['do_stend'])) {
  header("Location:http://$HTTP_HOST/stend.php?num_str=".$cgi['num_str']);
	exit();
}
if(isset($cgi['do_service'])) {
  header("Location:http://$HTTP_HOST/service.php?did=".$cgi['num_str']);
	exit();
}
if(isset($cgi['do_schet'])) {
  header("Location:http://$HTTP_HOST/schet.php?did=".$cgi['num_str']);
	exit();
}
if(isset($cgi['do_plateg'])) {
  header("Location:http://$HTTP_HOST/plateg.php?did=".$cgi['num_str']);
	exit();
}
if(isset($cgi['do_catalog'])) {
  header("Location:http://$HTTP_HOST/catalog.php?num_str=".$cgi['num_str']);
	exit();
}


$text_menu = $objAdmMenu->GetMenu($user_id, 'Договора с экспонентами');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

# echo "user_id = $user_id<br>";

$table = "dogovor";

$filds = array(
  'exhibition_id' => array('Проект','REF','exhibition','name'),
  'client_id' => array('Экспонент','REF','company','name'),
  'number' => array('Номер','NUM'),
  'date' => array('Дата','DATE'),
  'currency_id' => array('Валюта','REF','arrCurrency'),
  'vznos' => array('Регистрационный взнос','NUM'),
  'area_all' => array('Площадь в павильоне','NUM'),
  'area_open' => array('Открытая площадь','NUM'),
  'equipment_id' => array('Оборудование','REF','arrEquipment'),
  'discount' => array('Скидка','NUM'),
  'charge_poz' => array('Наценка за позиционирование','REF','arrCharge','',1),
  'manager_id' => array('Менеджер','REF','manager','name'),

);



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

$arr_1 = $arr_2 = $arr_3 = $arr_4 = array();
reset($filds);
while (list($k,$v) = each ($filds)) {
	array_push ($arr_1,$k);
	array_push ($arr_2,"'".$cgi[$k]."'");
	array_push ($arr_3,$k."='".$cgi[$k]."'");
  array_push ($arr_4,$filds[$k][0]."=".addslashes($cgi[$k]));
}

if(isset($cgi['add_row'])) {
  $sql = "insert into $table (".implode(",",$arr_1).", xml_flg) values (".implode(",",$arr_2).", 'Y')";
  my_exec($sql);
  $dogovor_id = mysql_insert_id();
  make_xml("dogovor",$dogovor_id);
/*
  $query = "insert into statistic set user_id=$user_id, date=now(), type='insert', text_new='".implode("<br>",$arr_4)."', sql='".addslashes($sql)."', comment='Добавлен договор'";
  my_exec($query);
  */
}

if(isset($cgi['upd_row'])) {
  /*
  $sql_old = "select * from $table where id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  foreach($row_old as $k=>$v) {
    $text_old .= ($filds[$k][0]?$filds[$k][0]."=".$v."<br>":"");
  }
*/
	$sql = "update $table set ".implode(",",$arr_3).", xml_flg='Y' where id=".$cgi['num_str'];
	my_exec($sql);
  make_xml("dogovor",$cgi['num_str']);
/*
  $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='".$text_old."', text_new='".implode("<br>",$arr_4)."', sql='".addslashes($sql)."', comment='Обновлен договор'";
  my_exec($query);
  */
}

if(isset($cgi['del_row'])) {
	$is_link=0;
  /*
	$query = "select id from place where prihod_id = ".$cgi['num_str']." limit 1";
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Места, "; }
	$query = "select id from plateg where prihod_id = ".$cgi['num_str']." limit 1";
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Платежи, "; }
	$query = "select id from schet where prihod_id = ".$cgi['num_str']." limit 1";
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Счета, "; }
	$query = "select id from spec_prihod where prihod_id = ".$cgi['num_str']." limit 1";
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Спецификации, "; }
	$query = "select id from badgik where prihod_id = ".$cgi['num_str']." limit 1";
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Баджики, "; }
	$query = "select id from publication where prihod_id = ".$cgi['num_str']." limit 1";
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Публикации, "; }
  */
	if ($is_link) {
    $error=red2("Запись не может быть удалена, т.к. существуют связанные с ней записи.<br>".substr($whereIt,0,-2)."<br>Сперва удалите их!");
  } else {
#		$sql = "delete from $table where id = ".$cgi['num_str'];
		$sql = "update $table set status_id=1 where id = ".$cgi['num_str'];
		my_exec($sql);

		$query = "insert into statistic set user_id=$user_id, date=now(), type='hide', text_old='status_id=0', text_new='status_id=1', text_sql='".addslashes($sql)."', comment='Скрыт договор'";
		my_exec($query);
	}
}

if(isset($cgi['drop_row'])) {
  $sql_old = "select * from $table where id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  foreach($row_old as $k=>$v) {
    $text_old .= ($filds[$k][0]?$filds[$k][0]."=".$v."<br>":"");
  }

	$sql = "delete from $table where id = ".$cgi['num_str'];
	my_exec($sql);

  $query = "insert into statistic set user_id=$user_id, date=now(), type='delete', text_old='".$text_old."', text_sql='".addslashes($sql)."', comment='Удален договор'";
  my_exec($query);
}

if(!isset($cgi['page'])) {$cgi['page']='main';}

switch($cgi['page']){
case 'main':
  show();
  break;
case 'info':
  info($cgi['num_str']);
  break;
case 'upd':
  add($cgi['num_str']);
  break;
case 'add':
  add(0);
  break;
default:
  show();
}
include_once("usr_templ.php");


#######################################################################


function add($num_str) {
  global $filds, $body, $table, $cgi, $arrUser, $lists, $user_id, $arrManager, $this_project;
	reset($filds);
	while (list($k,$v) = each($filds)) {
		$$k=($cgi[$k]?$cgi[$k]:"");
	}
  $vznos = 1;
  /*
  if ($cgi[search_exhibition_id]) {
    $exhibition_id = $cgi[search_exhibition_id];
    $sql = "select max(number) from $table where exhibition_id='".$exhibition_id."'";
    $sth = my_exec($sql);
    $number = mysql_result($sth, 0) + 1;
  }
*/
  if ($arrUser[1] == 'm') {
	  $manager_id = $arrUser[0];
  }

  $header = "Добавление договора с экспонентами";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select $table.* from $table where $table.id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
		$header = "Редактирование договора с экспонентами";
		reset($filds);
		while (list($k,$v) = each($filds)) {
			$$k=($cgi[$k]?$cgi[$k]:$row[$k]);
		}
#		$postfix = $row[postfix];
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }

	$body = "<center><h4>$header</h4>";
  $body .= "<form name=form_1 method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<input type=hidden name=page value=''>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	reset($filds);
	while (list($k,$v) = each($filds)) {
		if ($k=='exhibition_id') {
      $sql_e = "select name from exhibition where id = '".$this_project."'";
      $sth_e = my_exec($sql_e);
      $name_project = mysql_result($sth_e, 0);
			$body .= "<tr valign=top><td><b>$v[0]</b></td><td><b>".$name_project."</b></td></tr>\n";
    } elseif ($k=='client_id') {
      if ($arrUser[1] == 'm') { # Manager
			  $sql = "select id, name from company where manager_id='".$arrUser[0]."' order by name";
      } else { # Not manager
			  $sql = "select id, name from company order by name";
      }
			$sth = my_exec($sql);
			$arr=array();
			while($row = mysql_fetch_array($sth)) {
				$arr[$row[id]]=$row[name];
			}
			$body .= "<tr valign=top><td><b>".$v[0]."</b></td><td>".do_combobox($k,$arr,$$k,'',0)."</td></tr>\n";
		} elseif ($k=='number') {
      if ($$k) {
			  $body .= "<tr valign=top height=33><td><b>".$v[0]."</b></td><td><b>".make_num($$k)."</b><input type=hidden name='".$k."' value='".$$k."'></td></tr>\n";
      } else {
			  $body .= "<tr valign=top height=33><td><b>".$v[0]."</b></td><td><b>Номер добавится автоматически...</b></td></tr>\n";
      }
    } elseif ($k=='manager_id') {
			$body .= "<tr valign=top><td><b>".$v[0]."</b></td><td>".do_combobox($k,$arrManager,$$k,'',0)."</td></tr>\n";
		} else {
			$body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input($k,$$k,$v[1],$v[2],$v[3],20,$v[4])."</td></tr>\n";
		}
	}

	$body .= "</table><BR>\n";
	$body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
	$body .= "<br><br>\n";
	if (!$num_str) {$disabled="disabled=true";}

  $body .= "<input type=submit name=do_stend value='Стенд' $disabled>&nbsp;&nbsp;&nbsp;\n";
  $body .= "<input type=submit name=do_service value='Доп. услуги и оборудование' $disabled>&nbsp;&nbsp;&nbsp;\n";
//  $body .= "<input type=submit name=do_schet value='Счета' $disabled>&nbsp;&nbsp;&nbsp;\n";
//  $body .= "<input type=submit name=do_plateg value='Платежи' $disabled $disabled2>&nbsp;&nbsp;&nbsp;\n";
  $body .= "<input type=submit name=do_catalog value='Каталог' $disabled $disabled2>&nbsp;&nbsp;&nbsp;\n";

#	if ($arrUser[1] == 'm') {$disabled2="disabled=true";}
#	$body .= "<input type=submit name=do_place value='Места' $disabled>&nbsp;&nbsp;&nbsp;\n";
#	$body .= "<input type=submit name=do_specif value='Спецификации' $disabled>&nbsp;&nbsp;&nbsp;\n";
#	$body .= "<input type=submit name=do_schet value='Счета' $disabled>&nbsp;&nbsp;&nbsp;\n";
#	$body .= "<input type=submit name=do_plateg value='Платежи' $disabled $disabled2>&nbsp;&nbsp;&nbsp;\n";
#	$body .= "<input type=submit name=do_public value='Публикации' $disabled>&nbsp;&nbsp;&nbsp;\n";
#	$body .= "<input type=submit name=do_badjik value='Баджики' $disabled>&nbsp;&nbsp;&nbsp;\n";
  $body .= "</form></center>\n";
  return $body;
}

function info($num_str) {
  global $filds, $body, $table, $cgi, $arrUser, $lists, $user_id, $arrManager;

  $sql = "select id, name from company";
  $sth = my_exec($sql);
  $arrCompany = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrCompany[$row[id]] = $row[name];
  }

  $sql = "select id, name from exhibition";
  $sth = my_exec($sql);
  $arrExhibition = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrExhibition[$row[id]] = $row[name];
  }

  $sql = "select $table.* from $table where $table.id=$num_str";
  #		echo $sql;
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  $header = "Информация по договору с экспонентами";
  reset($filds);
  while (list($k,$v) = each($filds)) {
    $$k=($cgi[$k]?$cgi[$k]:$row[$k]);
  }
#		$postfix = $row[postfix];
  $name_btn = "upd_row";
  $text_btn = "Обновить";


	$body = "<center><h4>$header</h4>";
  $body .= "<form name=form_1 method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<input type=hidden name=page value=''>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	reset($filds);
	while (list($k,$v) = each($filds)) {
		if ($k=='exhibition_id') {
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

	$body .= "</table><BR>\n";
	$body .= "<br><br>\n";
  $body .= "</form></center>\n";
  return $body;
}

function show() {
  global $body, $table, $cgi, $user_id, $arrUser, $lists, $arrInfo, $arrData, $cmp_desc, $this_project;
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


	$arr_search = array(
		'manager_id'=>array('Менеджер','REF','company','name',1),
		);


  if ($arrUser[1] != 'm') { # НЕ манагер
  	$body = do_search_here($arr_search);
  }
  $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1><td width=30 rowspan=2>&nbsp;</td>";
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
  $body .= "<td rowspan=2><a href='#' onClick=\"document.form_search.ordCol.value='number'; document.form_search.submit();return false;\"><b><font color=white>№</font></b></a>&nbsp;&nbsp;$str</td>";

#	$body .= "<td><b>Дата</b></td>";
  if($cgi[oldOrdCol] == 'date'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td rowspan=2><a href='#' onClick=\"document.form_search.ordCol.value='date'; document.form_search.submit();return false;\"><b><font color=white>Дата</font></b></a>&nbsp;&nbsp;$str</td>";

#	$body .= "<td><b>Экспонент</b></td>";
  if($cgi[oldOrdCol] == 'client_id'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td rowspan=2><a href='#' onClick=\"document.form_search.ordCol.value='client_id'; document.form_search.submit();return false;\"><b><font color=white>Экспонент</font></b></a>&nbsp;&nbsp;$str</td>";

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

  $body .= "</tr>";
  $body .= "<tr valign=top align=center class=tr1><td><b>стандарт</b></td><td><b>октонорм</b></td><td><b>руб.</b></td><td><b>&euro;</b></td><td><b>руб.</b></td><td><b>&euro;</b></td></tr>";
/*
	reset($arr_search);
	while (list($k,$v) = each($arr_search)) {
#		echo "$k = $v[1]<br>";
    if ($cgi["search_".$k] || $cgi["search_".$k."_d_1"]) {
      switch($v[1]){
        case 'STR':
          $arrWhere[] = $table.".".$k." like '%".$cgi["search_".$k]."%'";
          break;
        case 'TEXT':
          $arrWhere[] = $table.".".$k." like '%".$cgi["search_".$k]."%'";
          break;
        case 'NUM':
          $arrWhere[] = $table.".".$k."='".$cgi["search_".$k]."'";
          break;
        case 'DATE':
          $data_1 = $cgi["search_".$k."_y_1"]."-".$cgi["search_".$k."_m_1"]."-".$cgi["search_".$k."_d_1"];
          $data_2 = $cgi["search_".$k."_y_2"]."-".$cgi["search_".$k."_m_2"]."-".$cgi["search_".$k."_d_2"];
          $arrWhere[] = $table.".".$k.">='".$data_1."'";
          $arrWhere[] = $table.".".$k."<='".$data_2."'";
          break;
        case 'REF':
          $arrWhere[] = $table.".".$k."='".$cgi["search_".$k]."'";
          break;
        default:
          $arrWhere[] = $table.".".$k."='".$cgi["search_".$k]."'";
      }
		}
	}
  if (count($arrWhere)) {
		$where = "and ".join(" and ",$arrWhere);
	}
  */

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

  if ($cgi['search_manager_id']) {
		$where .= " and c.manager_id=".$cgi['search_manager_id'];
	}

  if ($cgi["btn_all"]) {
		$where = "";
	}

  $where_main = " and $table.exhibition_id=".$this_project;
  if ($arrUser[1] == 'm') {
		$where_main .= " and c.manager_id=".$arrUser[0];
	}

	$sql = "select $table.*, c.name as c_name from $table, company c where c.id=$table.client_id $where_main $where $order";
#			echo $sql;
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
    $arrAll[$row[id]] = $row;
  }
  #print_arr($arrAll);

  if($cgi[oldOrdCol] == 'summ'){
#    echo $cgi[ordDesc];
    $cmp_desc = $cgi[ordDesc];
    usort($arrAll, "cmp");
  }

  foreach ($arrAll as $k=>$row) { # Сперва все действующие
    if ($row[status_id] == 0) {
      $s1 = $s2 = $bg = "";

      $body .= "<tr valign=top align=center $bg><td nowrap><a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно, хотите аннулировать договор?')\"><img src='/images/del.gif' border=0></a>";
      $body .= "</td>";

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
      $body .= "</tr>\n";

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
    }
	}

  foreach ($arrAll as $k=>$row) { # Затем не действующие
    if ($row[status_id] == 1) {
      $s1 = "<s>";
      $s2 = "</s>";
      $bg = "bgcolor=dddddd";

      $body .= "<tr valign=top align=center $bg><td nowrap>";
      if ($arrUser[1] == 'a') {
        $body .= "<a href='$PHP_SELF?drop_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Договор будет УДАЛЕН! Продолжить?')\"><img src='/images/del_red.gif' border=0></a>";
      } else {
        $body .= "&nbsp;";
      }
      $body .= "</td>";

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
    }
  }

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
	$body .= "</table>\n";
  return $body;
}

function do_search_here($filds) {
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