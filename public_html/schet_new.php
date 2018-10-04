<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

$text_menu = $objAdmMenu->GetMenu($user_id, 'Счета');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "schet";

$filds = array(

#  'name'=>array('Референс','STR'),
  'number'=>array('Номер (авто)','STR'),
  'number2'=>array('Номер (ручн.)','STR','','',10),
  'date'=>array('Дата счета','DATE'),
  'dogovor_id'=>array('Плательщик','REF','dogovor','number'),
  'note'=>array('Наименование','STR'),
  'summ'=>array('Сумма','STR'),
  'currency_id'=>array('Валюта','REF','arrCurrency'),

);

if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

if(isset($cgi['add_row'])) {
  $sql = "select id from dogovor where exhibition_id = '".$this_project."'";
  $sth = my_exec($sql);
  $arr = array();
  while ($row = mysql_fetch_array($sth)) {
    $arr[$row[id]] = $row[id];
  }

  $sql = "select max(number) from $table where dogovor_id in (".join(",",$arr).")";
  $sth = my_exec($sql);
  $number = mysql_result($sth, 0) + 1;

	if ($cgi['summ']) {
		$sql = "insert into $table set dogovor_id = '".$cgi[dogovor_id]."', number = '".$number."', number2 = '".$cgi[number2]."', date = '".$cgi[date]."', note = '".addslashes($cgi[note])."', summ = '".$cgi[summ]."', currency_id = '".$cgi[currency_id]."'";
		my_exec($sql);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_row'])) {
	if ($cgi['summ']) {
		$sql = "update $table set dogovor_id = '".$cgi[dogovor_id]."', number2 = '".$cgi[number2]."', date = '".$cgi[date]."', note = '".addslashes($cgi[note])."', summ = '".$cgi[summ]."', currency_id = '".$cgi[currency_id]."' where id=".$cgi['num_str'];
		my_exec($sql);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_row'])) { # Скрыть счет
  $sql = "update $table set status=1 where id = ".$cgi['num_str'];
  my_exec($sql);
}

if(isset($cgi['drop_row'])) { # Удалить счет
	$sql = "delete from $table where id = ".$cgi['num_str'];
	my_exec($sql);
}

$sql = "select * from price_main";
$sth = my_exec($sql);
$arrInfo = array();
while ($row = mysql_fetch_array($sth)) {
	$arrInfo[$row[exhibition_id]][$row[service_id]][rub] = $row[price_rub];
	$arrInfo[$row[exhibition_id]][$row[service_id]][eur] = $row[price_eur];
}

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

#print_arr($arrServ);

$sql = "select company.name as c_name, dogovor.* from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' order by company.name, dogovor.number";
$sth = my_exec($sql);
$arrClient = $arrMoney = array();
while ($row = mysql_fetch_array($sth)) {
  $arrClient[$row[id]] = stripslashes($row[c_name])." (договор № ".$row[number].")";

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
  #if ($row[id] == 274) { echo $arrInfo[$row[exhibition_id]][1][rub]." - ".$row['vznos']; }
  if ($row['currency_id'] == 2) { # EURO
    $arrMoney[$row[id]][summ] = number_format((($arrInfo[$row[exhibition_id]][1][eur] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][eur] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][eur] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][eur] * $s_st + $arrInfo[$row[exhibition_id]][5][eur] * $s_okt + $arrServ[$row[id]])),2,'.',''); #  * 1.18
    $arrMoney[$row[id]][curr] = "euro";
  } else { # RUB
    $arrMoney[$row[id]][summ] = number_format((($arrInfo[$row[exhibition_id]][1][rub] * $row['vznos'] + ($arrInfo[$row[exhibition_id]][2][rub] * $s_pav * (1 + $row[charge_poz] / 100) + $arrInfo[$row[exhibition_id]][3][rub] * $s_otk) * (1 - $row[discount] / 100) + $arrInfo[$row[exhibition_id]][4][rub] * $s_st + $arrInfo[$row[exhibition_id]][5][rub] * $s_okt + $arrServ[$row[id]])),2,'.',''); #  * 1.18
    $arrMoney[$row[id]][curr] = "руб.";
  }

}

$sql = "select schet.summ, dogovor.id from dogovor, schet where dogovor.id = schet.dogovor_id and dogovor.exhibition_id = '".$this_project."'";
$sth = my_exec($sql);
while ($row = mysql_fetch_array($sth)) {
  $arrMoney[$row[id]][schet] += $row[summ];
}
#print_arr($arrMoney);

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
  global $filds, $body, $table, $arrUser, $arrManager, $lists, $this_project, $arrClient, $arrMoney;
	reset($filds);
	while (list($k,$v) = each($filds)) {
		$$k='';
	}
  //if ($arrUser[1] == 'm') {
	//  $manager_id = $arrUser[0];
  //}

  $header = "Добавление счета";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
		$header = "Редактирование счета";
		reset($filds);
		while (list($k,$v) = each($filds)) {
			$$k=stripslashes($row[$k]);
#      echo $kk." = ".$row[$k];
		}
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }
	$body = "<center><h4>$header</h4>";


  $body .= "
  <script language=javascript>
  var arrS = new Array();
  var arrD = new Array();
  var arrC = new Array();
  ";
  foreach($arrMoney as $km=>$vm) {
    $body .= "arrS[".$km."] = '".$vm[summ]." ".$vm[curr]."'\n";
    $body .= "arrD[".$km."] = '".number_format(($vm[summ]-$vm[schet]),2,'.','')."';\n";
    $body .= "arrC[".$km."] = '".($vm[curr])."';\n";
  }
  $body .= "

  function set_info() {
    dog = document.f1.dogovor_id.value;
    if (document.getElementById) {
      var fe01 = document.getElementById('fe1');
      var fe02 = document.getElementById('fe2');
    } else if (document.all) {
      var fe01 = document.all['fe1'];
      var fe02 = document.all['fe2'];
    }
    fe01.innerHTML = arrS[dog];
    fe02.innerHTML = arrD[dog]+' '+arrC[dog];
  }
  function make_recount() {
    dog = document.f1.dogovor_id.value;
    if (document.getElementById) {
      var fe02 = document.getElementById('fe2');
    } else if (document.all) {
      var fe02 = document.all['fe2'];
    }
    summ = document.f1.summ.value;
    fe02.innerHTML = (arrD[dog]-summ)+' '+arrC[dog];
  }
  </script>
  ";

  $body .= "<form name=f1 method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";

  if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    reset($filds);
    while (list($k,$v) = each($filds)) {
      if ($k == 'number') {
        if (!$num_str) {
          $sql = "select id from dogovor where exhibition_id = '".$this_project."'";
          $sth = my_exec($sql);
          $arr = array();
          while ($row = mysql_fetch_array($sth)) {
            $arr[$row[id]] = $row[id];
          }

          $sql = "select max(number) from $table where dogovor_id in (".join(",",$arr).")";
          $sth = my_exec($sql);
          $number = mysql_result($sth, 0) + 1;
        }
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td><b>".$number."</b></td></tr>\n";
      } elseif ($k == 'dogovor_id') {


        $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_combobox('dogovor_id',$arrClient,$dogovor_id,' style="width:450px" onChange=set_info();','')."</td></tr>\n";
      } else {
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input($k,$$k,$v[1],$v[2],$v[3],$v[4],$v[5])."</td></tr>\n";
      }
    }
    $body .= "</table><BR>\n";
    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr valign=top><td><b>Сумма договора: </b></td><td><b><span id=fe1>&nbsp;</span></b></td></tr>\n";
    $body .= "<tr valign=top><td><b>Остаток по договору: </b></td><td><b><span id=fe2>&nbsp;</span></b></td></tr>\n";
    $body .= "</table><BR>\n";
    if (!$num_str) {
      $body .= "<input type=button name=recount value='  Пересчитать  ' onClick=make_recount();>&nbsp;&nbsp;&nbsp;";
    }
    $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  } else {
    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    reset($filds);
    while (list($k,$v) = each($filds)) {
      if ($k == 'number') {
        if (!$num_str) {
          $sql = "select id from dogovor where exhibition_id = '".$this_project."'";
          $sth = my_exec($sql);
          $arr = array();
          while ($row = mysql_fetch_array($sth)) {
            $arr[$row[id]] = $row[id];
          }

          $sql = "select max(number) from $table where dogovor_id in (".join(",",$arr).")";
          $sth = my_exec($sql);
          $number = mysql_result($sth, 0) + 1;
        }
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td><b>".$number."<input type=hidden name=dogovor_id value='".$dogovor_id."'></b></td></tr>\n";
      } elseif ($k == 'dogovor_id') {
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td><b>".$arrClient[$dogovor_id]."</b>&nbsp;</td></tr>\n";
      } elseif ($k == 'date') {
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td><b>".mysql2date2($$k)."</b>&nbsp;</td></tr>\n";
      } elseif ($k == 'currency_id') {
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td><b>".$lists['arrCurrency'][$$k]."</b>&nbsp;</td></tr>\n";
      } else {
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td><b>".$$k."</b>&nbsp;</td></tr>\n";
      }
    }
    $body .= "</table><BR>\n";
    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr valign=top><td><b>Сумма договора: </b></td><td><b><span id=fe1>&nbsp;</span></b></td></tr>\n";
    $body .= "<tr valign=top><td><b>Остаток по договору: </b></td><td><b><span id=fe2>&nbsp;</span></b></td></tr>\n";
    $body .= "</table><BR>\n";
    $body .= "<input type=button name=recount value='  Вернуться  ' onClick='history.back()'>";

  }
  $body .= "</form></center>\n";
  $body .= "<script language=javascript>set_info();</script>";
  return $body;
}


function show() {
  global $body, $table, $cgi, $arrUser, $this_project, $arrMoney, $lists;
#  print_arr($arrUser);

	$body = do_search_here();
  #if ($arrUser[1] == 'm' || $arrUser[1] == 'a') {
  if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
    $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
  } else {
    $body .= "<br><br>";
  }
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  $body .= "<td width=30><b>№</b></td>";
  if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
	  $body .= "<td width=30>&nbsp;</td>";
  }

  if($cgi[oldOrdCol] == 'number'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='number'; document.form_search.submit();return false;\"><b><font color=white>Номер (авто)</font></b></a>&nbsp;&nbsp;$str</td>";

  if($cgi[oldOrdCol] == 'number2'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='number2'; document.form_search.submit();return false;\"><b><font color=white>Номер (ручн.)</font></b></a>&nbsp;&nbsp;$str</td>";

  if($cgi[oldOrdCol] == 'date'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='date'; document.form_search.submit();return false;\"><b><font color=white>Дата</font></b></a>&nbsp;&nbsp;$str</td>";

	$body .= "<td><b>Плательщик</b></td>";
	$body .= "<td><b>Сумма, руб.</b></td>";
	$body .= "<td><b>Сумма, euro</b></td>";
	$body .= "<td><b>Долг</b></td>";
  $body .= "</tr>";

  if ($arrUser[1] == 'm') {
	  $manager_id = $arrUser[0];
    $where_main = " and dogovor.manager_id='".$manager_id."'";
  }

/*
  if ($cgi["search_manager_id"]) {
    $arrWhere[] = $table.".manager_id='".$cgi["search_manager_id"]."'";
  }
  if ($cgi["search_invite"]) {
    $arrWhere[] = $table.".is_invite='".$cgi["search_invite"]."'";
  }
  if ($cgi["search_work_type"]) {
    $arrWhere[] = $table.".work_type like '%:".$cgi["search_work_type"].":%'";
  }
*/
  if ($cgi["search_number"]) {
    $arrWhere[] = $table.".number = '".$cgi["search_number"]."'";
  }

	if($cgi[oldOrdCol]){
		if ($cgi[oldOrdCol] == 'name') {
			$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
		}	else {
			$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
		}
  } else {
    $order = " order by $table.id desc";
  }
  if (count($arrWhere)) {
		$where = "and ".join(" and ",$arrWhere);
	}
  if ($cgi["btn_all"]) {
		$where = "";
	}

  $i = 0;
	$sql = "select $table.*, dogovor.id as d_id, dogovor.number as d_num, company.name as c_name from $table, dogovor, company where company.id = dogovor.client_id and dogovor.id = $table.dogovor_id and dogovor.exhibition_id=".$this_project." $where_main $where $order";
#		echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
    if ($row[status] == 1) {
      $s1 = "<s>";
      $s2 = "</s>";
      $bg = "bgcolor=dddddd";
    } else {
      $s1 = $s2 = $bg = "";
      if ($row[currency_id] == 2) {
        $all_summ_euro += $row[summ];
        $all_dolg_euro += $arrMoney[$row[d_id]][summ] - $arrMoney[$row[d_id]][schet];
      } else {
        $all_summ_rub += $row[summ];
        $all_dolg_rub += $arrMoney[$row[d_id]][summ] - $arrMoney[$row[d_id]][schet];
      }
    }
		$body .= "<tr valign=top $bg>";
    $body .= "<td align=center>".$s1.++$i.$s2.".</td>";
    if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
      $body .= "<td align=center nowrap>".$s1;

      if (!$row['status']) {
        $body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."&did=".$cgi[d_id]."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
      } else {
        $body .= "<a href='$PHP_SELF?drop_row=yes&num_str=".$row['id']."&did=".$cgi[d_id]."' onclick=\"return confirm('Договор будет УДАЛЕН! Продолжить?')\"><img src='/images/del_red.gif' border=0></a> "; 
      }
      #$body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
      $body .= $s2."</td>";
    }
    $body .= "<td align=center>".$s1."<a href='$PHP_SELF?page=upd&num_str=".$row['id']."'>".$row[number]."</a>".$s2."&nbsp;</td><td>".$s1.$row[number2].$s2."&nbsp;</td><td>".$s1.mysql2date2($row[date]).$s2."&nbsp;</td><td>".$s1.stripslashes($row[c_name])." (договор № ".$row[d_num].")".$s2."</td>";
    if ($row[currency_id] == 2) {
      $body .= "<td>&nbsp;</td>";
      $body .= "<td>".$s1.$row[summ].$s2."&nbsp;</td>";
    } else {
      $body .= "<td>".$s1.$row[summ].$s2."&nbsp;</td>";
      $body .= "<td>&nbsp;</td>";
    }
    $body .= "<td>".$s1.number_format(($arrMoney[$row[d_id]][summ] - $arrMoney[$row[d_id]][schet]),2,'.','')." ".$arrMoney[$row[d_id]][curr].$s2."&nbsp;</td></tr>\n"; 
	}

  $body .= "<tr valign=top><td align=right colspan=6><b>Итого:</b> </td><td>$all_summ_rub&nbsp;</td><td>$all_summ_euro&nbsp;</td><td>$all_dolg_rub руб. + $all_dolg_euro &euro;</td></tr>\n"; 

	$body .= "</table>\n";
  return $body;
}

function do_search_here() {
	global $cgi, $table, $lists, $arrUser, $arrManager, $arrProject, $arrWorkType;
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
    $cgi["search_number"] = "";
  } elseif (!isset($cgi["search_manager_id"]) && $arrUser[1] == 'm') {
    $cgi["search_manager_id"] = $arrUser[0];
  }

  $cont .= "<tr><td><b>Номер</b>&nbsp;&nbsp;</td><td><input name=search_number value='".$cgi["search_number"]."' size='10'></tr>\n";

	$cont .= "</td></tr></table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
	$cont .= "<br><input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
	$cont .= "<input type=submit name=btn_search value='Поиск'>";
	$cont .= "</td></tr></table></form>\n";

	return $cont;
}

function do_input_here($name,$value,$type,$ref_1,$ref_2,$lenght,$size) {
#	echo "$name, $value, $type, $ref_1, $ref_2, $lenght, $first<br>";
	global $cgi,$lists;
	if (!$lenght) $lenght=70;
	if ($size) $maxsize=" maxlength=$size";
	switch($type){
		case 'STR':
			$cont = "<input name=$name value='".$value."' size='".$lenght."' $maxsize>";
			break;
		case 'TEXT':
			$cont = "<textarea name='$name' cols=50 rows=6>".$value."</textarea>";
			break;
		case 'NUM':
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
			break;
		case 'REF':
			if(isset($lists[$ref_1])) {
				$arr = $lists[$ref_1];
			} else {
				$sql = "select id, $ref_2 from $ref_1 order by $ref_2";
				$sth = my_exec($sql);
				while($row = mysql_fetch_array($sth)) {
					$arr[$row['id']] = stripslashes($row[$ref_2]);
				}
			}
			$cont = do_combobox($name,$arr,$value,'','');
			break;
		case 'MY':
      #echo "work_type = '".$work_type."'";

      $arrWT = array();
      if (strlen($value) > 2) {
        $arrTemp = split(":",substr($value,1,-1));
        foreach($arrTemp as $kwt=>$vwt) {
          $arrWT[] = $vwt;
        }
        #print_arr($arrWT);
      } else {

      }

      $sql = "select id, $ref_2 from $ref_1 order by $ref_2";
      $sth = my_exec($sql);
      while($row = mysql_fetch_array($sth)) {
        $arr[$row['id']] = stripslashes($row[$ref_2]);
      }

      $cont = "<select name='".$name."' size=4 multiple>\n";
      if($first) {$cont .= "<option value=''>---";}
      while(list($k,$v) = each($arr)){
        $cont .= "<option value='".$k."'";
        if (in_array($k,$arrWT)){
          $cont .= " selected ";
        }
        $cont .= ">".$v."\n";
      }
      $cont .= "</select>\n";
			break;
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}
?>