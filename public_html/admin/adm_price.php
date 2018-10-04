<?php

#include_once("adm_menu.inc");
include_once("../../private/conf.inc");
include_once("../../private/func.inc");
if (!authenticateAdmin ($cookie_adm, $cookie_adm_passwd)) {
	header("Location:http://$HTTP_HOST/admin/index.php");
	exit();
}
#$text_menu = $objAdmMenu->GetMenu('Выставки');
$text_menu = "";
$table = "price";

$cgi[exhibition_id] = $exhib = $cgi[exhib];
$sql = "select name from exhibition where id=".$exhib;
$sth = my_exec($sql);
$row = mysql_fetch_array($sth);
$exhib_name = $row['name'];

$filds = array(
  'exhibition_id' => array('Выставка','REF','exhibition','name'),
  'type_id' => array('Тип услуги','REF','razdel_price','name'),
  'name' => array('Название рус','STR'),
  'name_engl' => array('Название англ','STR'),
  'ed_izm' => array('Единица измерения','REF','arrEdIzm'),
#  'min' => array('Минимальный заказ','NUM'),
  'price_rub' => array('Цена за единицу, руб.','NUM'),
  'price_eur' => array('Цена за единицу, EURO','NUM'),
);

$arr_1 = $arr_2 = $arr_3 = array();
reset($filds);

while (list($k,$v) = each ($filds)) {
	array_push ($arr_1,$k);
	array_push ($arr_2,"'".$cgi[$k]."'");
	array_push ($arr_3,$k."='".$cgi[$k]."'");
}

if(isset($cgi['add_row'])) {
	if ($cgi['name']) {
		$query = "insert into $table (".implode(",",$arr_1).") values (".implode(",",$arr_2).")";
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_row'])) {
	if ($cgi['name']) {
		$query = "update $table set ".implode(",",$arr_3)." where id=".$cgi['num_str'];
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_row'])) {
#	$query = "select * from spec_prihod where price_id = ".$cgi['num_str'];
#	$result = my_exec($query);
#	if (mysql_num_rows($result)) { 
#    red("Запись не может быть удалена, т.к. существуют связанные с ней записи.<br>В разделе Спецификации к договору с экспонентами.<br>Сперва удалите их!");
#  } else {
		$query = "delete from $table where id = ".$cgi['num_str'];
		my_exec($query);
#	}
}

if(!isset($cgi['page'])) {$cgi['page']='main';}

switch($cgi['page']){
case 'main':
  show();  
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
include_once("adm_templ.php");


#######################################################################


function add($num_str) {
  global $filds, $body, $table, $exhib, $exhib_name;

	reset($filds);
	while (list($k,$v) = each($filds)) {
		$$k='';
	}

  $header = "Добавление пункта прайс-листа проекта $exhib_name";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
		$header = "Редактирование пункта прайс-листа проекта $exhib_name";
		reset($filds);
		while (list($k,$v) = each($filds)) {
			$$k=$row[$k];
		}
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }
	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=95% border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	reset($filds);
	while (list($k,$v) = each($filds)) {
		if ($k=='exhibition_id') {

		} else {
			$body .= "<tr valign=top><td><b>".$v[0]."</b></td><td>".do_input($k,$$k,$v[1],$v[2],$v[3],'','')."</td></tr>\n";
		}
	}
	$body .= "</table><BR>\n";
	$body .= "<input type=hidden name=exhib value='$exhib'>\n";
	$body .= "<input type=submit name=$name_btn value='  $text_btn  '>&nbsp;&nbsp;&nbsp;\n";
	$body .= "<input type=button value='Закрыть окно' onClick='window.close()'>";
  $body .= "</form></center>\n";
  return $body;
}

function show() {
  global $body, $table, $lists, $exhib, $exhib_name;
	$body = "<center><h3>Прайс-лист проекта ".$exhib_name."</h3></center>";
	$body .= "<script language=javascript>\n";
  $body .= "function do_copy_price() { w_o('adm_copy_price.php',400,300,''); return false;}\n";
  $body .= "</script>";
  $body .= "<a href='$PHP_SELF?exhib=$exhib&page=add'>Добавить запись</a><br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr align=center class=tr1><td width=50>&nbsp;</td>";
	$body .= "<td><b>Название</b></td>";
	$body .= "<td><b>Тип услуги</b></td>";
#	$body .= "<td><b>Мин</b></td>";
	$body .= "<td><b>Ед.изм.</b></td>";
	$body .= "<td><b>Цена, руб.</b></td>";
	$body .= "<td><b>Цена, &euro;</b></td>";
  $body .= "</tr>";

	$sql = "select $table.*, r.name as type_name from $table, razdel_price r where r.id=$table.type_id and $table.exhibition_id=$exhib order by type_name,$table.name";
	#		echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top align=center><td nowrap><a href='$PHP_SELF?exhib=$exhib&del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> <a href='$PHP_SELF?exhib=$exhib&page=upd&num_str=".$row['id']."'><img src='/images/edit.gif' border=0></a></td>";
    $body .= "<td align=left>".$row[name]."&nbsp;</td>";
		$body .= "<td align=left>".$row[type_name]."&nbsp;</td>";
#		$body .= "<td>".$row[min]."&nbsp;</td>";
		$body .= "<td>".$lists['arrEdIzm'][$row[ed_izm]]."&nbsp;</td>";
		$body .= "<td>".$row[price_rub]."&nbsp;</td>";
		$body .= "<td>".$row[price_eur]."&nbsp;</td></tr>\n"; 
	}
	$body .= "</table><a id=refresh href='$PHP_SELF?exhib=$exhib'></a>\n";
	$body .= "<center><form><input type=button value='Копировать прайс-лист' onClick='return do_copy_price();'></form></center>\n";
	$body .= "<center><form><input type=button value='Закрыть окно' onClick='window.close()'></form></center>\n";
  return $body;
}

?>