<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

if(isset($cgi['return_dogovor'])) {
  header("Location:http://$HTTP_HOST/dogovor.php?page=upd&num_str=".$cgi['did']);
	exit();
}

$sql = "select * from dogovor where id='".$cgi['did']."'";
$sth = my_exec($sql); 
$dogInfo = mysql_fetch_array($sth);

$sql = "select * from price_main where exhibition_id='".$dogInfo[exhibition_id]."'";
$sth = my_exec($sql);
$arrInfo = array();
while ($row = mysql_fetch_array($sth)) {
	$arrInfo[$row[service_id]][rub] = $row[price_rub];
	$arrInfo[$row[service_id]][eur] = $row[price_eur];
}

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

#print_arr($cgi);

$text_menu = $objAdmMenu->GetMenu($user_id, 'Договора с экспонентами');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "schet";


$cgi[search_date_1_def]='2000-01-01';
$cgi[search_date_2_def]='2010-01-01';
if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];
/*
if ($cgi[currency_id] == 1) { # Рубли
  $sum_rub = ereg_replace(",",".",$cgi['summ']);
  $sum_eur = 0;
} else {
  $sum_rub = 0;
  $sum_eur = ereg_replace(",",".",$cgi['summ']);
}
*/

$cgi['summ'] = ereg_replace(",",".",$cgi['summ']);

if(isset($cgi['add_row'])) {
  $sql = "select id from dogovor where exhibition_id = '".$dogInfo[exhibition_id]."'";
  $sth = my_exec($sql);
  $arr = array();
  while ($row = mysql_fetch_array($sth)) {
    $arr[$row[id]] = $row[id];
  }

  $sql = "select max(number) from $table where dogovor_id in (".join(",",$arr).")";
  $sth = my_exec($sql);
  $cgi[number] = mysql_result($sth, 0) + 1;

	$sql = "insert into $table set dogovor_id = '".$cgi[did]."', number = '".$cgi[number]."', date = '".$cgi[date]."', note = '".addslashes($cgi[note])."', summ = '".$cgi['summ']."'";
	my_exec($sql);

  $text_new = "Договор = ".$cgi[did]."<br>Номер счета = ".make_num($cgi[number])."<br>Дата счета = ".$cgi[date]."<br> Наименование = ".addslashes($cgi[note])."<br> summ = ".$cgi['summ'];
  $query = "insert into statistic set user_id=$user_id, date=now(), type='insert', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Добавлен счет'";
  my_exec($query);
}


if(isset($cgi['upd_row'])) {

  $sql_old = "select * from $table where id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  $text_old .= "Номер счета = ".make_num($row_old[number])."<br>";
  $text_old .= "Дата счета = ".$row_old[date]."<br>";
  $text_old .= "Наименование = ".addslashes($row_old[note])."<br>";
  $text_old .= "summ = ".$row_old['summ']."<br>";

	$sql = "update $table set number = '".$cgi[number]."', date = '".$cgi[date]."', note = '".addslashes($cgi[note])."', summ = '".$cgi['summ']."' where id=".$cgi['num_str'];
	my_exec($sql);

  $text_new = "Номер счета = ".make_num($cgi[number])."<br>Дата счета = ".$cgi[date]."<br> Наименование = ".addslashes($cgi[note])."<br> summ = ".$cgi['summ'];
  $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='".$text_old."', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Обновлен счет'";
  my_exec($query);
}

if(isset($cgi['del_row'])) {
  $sql = "update $table set status=1 where id = ".$cgi['num_str'];
  my_exec($sql);

  $query = "insert into statistic set user_id=$user_id, date=now(), type='hide', text_old='status=0', text_new='status=1', text_sql='".addslashes($sql)."', comment='Скрыт счет'";
  my_exec($query);
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

  $query = "insert into statistic set user_id=$user_id, date=now(), type='delete', text_old='".$text_old."', text_new='-', text_sql='".addslashes($sql)."', comment='Удален счет'";
  my_exec($query);
}
/*
if(isset($cgi['show_row'])) {
  $sql = "update $table set status=0 where id = ".$cgi['num_str'];
  my_exec($sql);
  $query = "insert into statistic set user_id=$user_id, date=now(), type='show', text_old='status=1', text_new='status=0', sql='".addslashes($sql)."', comment='Восстановлен счет'";
  my_exec($query);
}
*/
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
  global $filds, $body, $table, $lists, $cgi, $arrInfo, $sumServ, $dogInfo;

  $sql = "select * from plateg where dogovor_id = '".$cgi[did]."'";
  $sth = my_exec($sql);
  while ($row = mysql_fetch_array($sth)) {
    $summ_plateg += $row[summ];
  }

#  $sql = "select * from dogovor where id='".$cgi[did]."'";
#  $sth = my_exec($sql);
#  $row_d = mysql_fetch_array($sth);


  $s_otk = $dogInfo[area_open];
  $s_pav = $dogInfo[area_all];
  if ($dogInfo[equipment_id] == 2) { # Стандарт
    $s_st = $dogInfo[area_all];
    $s_okt = 0;
  } elseif ($dogInfo[equipment_id] == 3) { # Октонорм
    $s_st = 0;
    $s_okt = $dogInfo[area_all];
  } else { # Нет
    $s_st = 0;
    $s_okt = 0;
  }

#  $summ_serv_eur = number_format(($arrServ[$row_d[id]][eur] + ($arrServ[$row_d[id]][rub] / $kurs_euro)),2,'.','');
#  $summ_serv_rub = number_format(($arrServ[$row_d[id]][rub] + ($arrServ[$row_d[id]][eur] * $kurs_euro)),2,'.','');


  if ($dogInfo['currency_id'] == 2) { # Договор в EURO
    $summ_dog = number_format((($arrInfo[1][eur] * $dogInfo['vznos'] + ($arrInfo[2][eur] * $s_pav * (1 + $dogInfo[charge_poz] / 100) + $arrInfo[3][eur] * $s_otk) * (1 - $dogInfo[discount] / 100) + $arrInfo[4][eur] * $s_st + $arrInfo[5][eur] * $s_okt + $sumServ)),2,'.',''); # * 1.18
  } else { # Договор в RUB
    $summ_dog = number_format((($arrInfo[1][rub] * $dogInfo['vznos'] + ($arrInfo[2][rub] * $s_pav * (1 + $dogInfo[charge_poz] / 100) + $arrInfo[3][rub] * $s_otk) * (1 - $dogInfo[discount] / 100) + $arrInfo[4][rub] * $s_st + $arrInfo[5][rub] * $s_okt + $sumServ)),2,'.',''); # * 1.18
  }

  $sql = "select id from dogovor where exhibition_id = '".$dogInfo[exhibition_id]."'";
  $sth = my_exec($sql);
  $arr = array();
  while ($row = mysql_fetch_array($sth)) {
    $arr[$row[id]] = $row[id];
  }

  $sql = "select max(number) from $table where dogovor_id in (".join(",",$arr).")";
  $sth = my_exec($sql);
  $row[number] = mysql_result($sth, 0) + 1;

  $header = "Добавление счета";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id='".$num_str."'";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
    $header = "Редактирование счета";
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }



	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post enctype='multipart/form-data'>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top height=33><td><b>Номер счета</b></td><td><b>".make_num($row[number])."</b><input type=hidden name='number' value='".$row[number]."'></td></tr>\n";
	$body .= "<tr valign=top><td><b>Дата счета</b></td><td>".do_input('date',$row[date],'DATE','','','','')."</td></tr>\n";
	$body .= "<tr valign=top><td><b>Наименование</b></td><td><textarea name='note' cols=50 rows=3>".stripslashes($row[note])."</textarea></td></tr>\n";
	$body .= "<tr valign=top><td><b>Сумма</b></td><td><input name='summ' value='".$row[summ]."' size=10> ".$lists['arrCurrency'][$dogInfo[currency_id]]."</td></tr>\n";
#	$body .= "<tr valign=top><td><b>Валюта</b></td><td>".do_combobox('currency_id',$lists['arrCurrency'],$row[currency_id],'','')."</td></tr>\n";
  $body .= "<tr valign=top height=33><td><b>Сумма договора</b></td><td><b>".$summ_dog." ".$lists['arrCurrency'][$dogInfo[currency_id]]."</b></td></tr>\n";
  $body .= "<tr valign=top height=33><td><b>Остаток по договору</b></td><td><b>".($summ_dog - $summ_plateg)." ".$lists['arrCurrency'][$dogInfo[currency_id]]."</b></td></tr>\n";
	$body .= "</table><BR>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
}

function info($num_str) {
  global $filds, $body, $table, $lists, $cgi, $arrInfo, $sumServ, $dogInfo;

  $sql = "select * from plateg where dogovor_id = '".$cgi[did]."'";
  $sth = my_exec($sql);
  while ($row = mysql_fetch_array($sth)) {
    $summ_plateg += $row[summ];
  }

  $s_otk = $dogInfo[area_open];
  $s_pav = $dogInfo[area_all];
  if ($dogInfo[equipment_id] == 2) { # Стандарт
    $s_st = $dogInfo[area_all];
    $s_okt = 0;
  } elseif ($dogInfo[equipment_id] == 3) { # Октонорм
    $s_st = 0;
    $s_okt = $dogInfo[area_all];
  } else { # Нет
    $s_st = 0;
    $s_okt = 0;
  }

  if ($dogInfo['currency_id'] == 2) { # Договор в EURO
    $summ_dog = number_format((($arrInfo[1][eur] * $dogInfo['vznos'] + ($arrInfo[2][eur] * $s_pav * (1 + $dogInfo[charge_poz] / 100) + $arrInfo[3][eur] * $s_otk) * (1 - $dogInfo[discount] / 100) + $arrInfo[4][eur] * $s_st + $arrInfo[5][eur] * $s_okt + $sumServ)),2,'.',''); # * 1.18
  } else { # Договор в RUB
    $summ_dog = number_format((($arrInfo[1][rub] * $dogInfo['vznos'] + ($arrInfo[2][rub] * $s_pav * (1 + $dogInfo[charge_poz] / 100) + $arrInfo[3][rub] * $s_otk) * (1 - $dogInfo[discount] / 100) + $arrInfo[4][rub] * $s_st + $arrInfo[5][rub] * $s_okt + $sumServ)),2,'.',''); # * 1.18
  }


  $sql = "select * from $table where id='".$num_str."'";
  #		echo $sql;
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  $header = "Просмотр счетов по договору";

	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top height=33><td><b>Номер счета</b></td><td><b>".make_num($row[number])."</b></td></tr>\n";
	$body .= "<tr valign=top height=33><td><b>Дата счета</b></td><td>".mysql2date2($row[date])."</td></tr>\n";
	$body .= "<tr valign=top height=33><td><b>Наименование</b></td><td>".stripslashes($row[note])."</td></tr>\n";

	$body .= "<tr valign=top height=33><td><b>Сумма</b></td><td>".$row[summ]." ".$lists['arrCurrency'][$dogInfo[currency_id]]."</td></tr>\n";
  $body .= "<tr valign=top height=33><td><b>Сумма договора</b></td><td><b>".$summ_dog." ".$lists['arrCurrency'][$dogInfo[currency_id]]."</b></td></tr>\n";
  $body .= "<tr valign=top height=33><td><b>Остаток по договору</b></td><td><b>".($summ_dog - $summ_plateg)." ".$lists['arrCurrency'][$dogInfo[currency_id]]."</b></td></tr>\n";
	$body .= "</table><BR>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
}


function show() {
  global $body, $table, $cgi, $arrUser, $lists, $dogInfo;
  $body = "<center><h4>Счета</h4></center>";
#  print_arr($arrUser);

  if ($arrUser[1] == 'b') {
    $body .= "<a href='$PHP_SELF?page=add&did=".$cgi[did]."'>Добавить запись</a><br><br>";
  } else {
    $body .= "<br><br>";
  }
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  if ($arrUser[1] == 'b') {
	  $body .= "<td width=30>&nbsp;</td>";
  }
	$body .= "<td><b>номер</b></td>";
	$body .= "<td><b>дата</b></td>";
	$body .= "<td><b>сумма</b></td>";
	$body .= "<td><b>оплата (да/нет/частично)</b></td>";
  $body .= "</tr>";

	$sql = "select * from $table where $table.dogovor_id='".$cgi['did']."' order by $table.id desc";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
    if ($row[status] == 1) {
      $s1 = "<s>";
      $s2 = "</s>";
      $bg = "bgcolor=dddddd";
    } else {
      $s1 = $s2 = $bg = "";
    }
		$body .= "<tr valign=top $bg>";
    if ($arrUser[1] == 'b') {
      $body .= "<td align=center nowrap>";
      if (!$row['status']) {
        $body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."&did=".$cgi[did]."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
      } else {
        $body .= "<a href='$PHP_SELF?drop_row=yes&num_str=".$row['id']."&did=".$cgi[did]."' onclick=\"return confirm('Договор будет УДАЛЕН! Продолжить?')\"><img src='/images/del_red.gif' border=0></a> "; 
      }
      $body .= "</td>";
    }

    if ($arrUser[1] == 'b') {
      $body .= "<td>$s1<a href='$PHP_SELF?page=upd&num_str=".$row['id']."&did=".$cgi[did]."'>".make_num($row[number])."</a>$s2&nbsp;</td>";
    } else {
      $body .= "<td>$s1<a href='$PHP_SELF?page=info&num_str=".$row['id']."&did=".$cgi[did]."'>".make_num($row[number])."</a>$s2&nbsp;</td>";
    }
    $body .= "<td>$s1".mysql2date2($row[date])."$s2&nbsp;</td>";
    $body .= "<td>$s1".$row[summ]." ".$lists['arrCurrency'][$dogInfo[currency_id]]."$s2&nbsp;</td>";
    $body .= "<td>$s1&nbsp;$s2</td></tr>\n"; 
	}
	$body .= "</table>\n";
  $body .= "<form method=post><center><br>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
}



?>