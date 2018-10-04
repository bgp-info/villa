<?php

include_once("adm_menu.inc");
include_once("../../private/conf.inc");
include_once("../../private/func.inc");
if (!authenticateAdmin ($cookie_adm, $cookie_adm_passwd)) {
	header("Location:http://$HTTP_HOST/admin/index.php");
	exit();
}
$text_menu = $objAdmMenu->GetMenu('Разделы прайс-листа');
$table = "razdel_price";

if ($cgi['check_place']) {
	$cgi['for_place']='Y';
} else {
	$cgi['for_place']='N';
}

if(isset($cgi['add_row'])) {
	if ($cgi['name']) {
		$query = "insert into $table (name, for_place) values ('".$cgi[name]."','".$cgi[for_place]."')";
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_row'])) {
	if ($cgi['name']) {
		$query = "update $table set name='".$cgi[name]."', for_place='".$cgi[for_place]."' where id=".$cgi['num_str'];
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_row'])) {
  $is_link=0;
	$sql = "select * from price where type_id = ".$cgi['num_str'];
	$sth = my_exec($sql);
	if (mysql_num_rows($sth))  {
    red("Запись не может быть удалена, т.к. имеются связанные с ней позиции прайс-листов!!!"); 
  } else {
    $query = "delete from $table where id = ".$cgi['num_str'];
    my_exec($query);
  }
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
  global $body, $table;
  $name = $check = '';
  $header = "Добавление раздела прайс-листа";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
		$header = "Редактирование раздела прайс-лист";
    $name=$row['name'];
    $check=$row['for_place'];
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }
	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=0 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	$body .= "<tr valign=top><td><b>Название:</b>&nbsp;&nbsp;<input name=name value='$name' size=69></td></tr>\n"; 
#	$body .= "<tr valign=top><td><b>Используется ли для характеристики мест?</b>&nbsp;&nbsp;<input type=checkbox name=check_place ".($check=='Y'?'checked':'')."></td></tr>\n"; 
	$body .= "</table><BR>\n";
	$body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "</form></center>\n";
  return $body;
}

function show() {
  global $body, $table, $cgi;
	$arr_search = array(
		'name'=>array('Название','STR'),
		);
	$body = do_search($arr_search);
  $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1><td width=50>&nbsp;</td>";
	$body .= "<td><b>Название</b></td>";
#	$body .= "<td><b>Используется ли для характеристики мест?</b></td>";
  $body .= "</tr>";
	reset($arr_search);
	while (list($k,$v) = each($arr_search)) {
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
		$where = "where ".join(" and ",$arrWhere);
	}
  if ($cgi["btn_all"]) {
		$where = "";
	}
	$arrPlace = array( 'N'=>'Не используется', 'Y'=>'Используется');
	$sql = "select * from $table $where order by name";
	#		echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top><td align=center nowrap><a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> <a href='$PHP_SELF?page=upd&num_str=".$row['id']."'><img src='/images/edit.gif' border=0></a></td>";
    $body .= "<td>".$row['name']."&nbsp;</td>";
#    $body .= "<td align=center>".$arrPlace[$row['for_place']]."&nbsp;</td>";
    $body .= "</tr>\n"; 
	}
	$body .= "</table>\n";
  return $body;
}

?>