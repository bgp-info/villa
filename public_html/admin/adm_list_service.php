<?php

include_once("adm_menu.inc");
include_once("../../private/conf.inc");
include_once("../../private/func.inc");
if (!authenticateAdmin ($cookie_adm, $cookie_adm_passwd)) {
	header("Location:http://$HTTP_HOST/admin/index.php");
	exit();
}
$text_menu = $objAdmMenu->GetMenu('Услуги, оборудование');
$table = "info";
$cgi['price_rub'] = ereg_replace(",",".",$cgi['price_rub']);
$cgi['price_eur'] = ereg_replace(",",".",$cgi['price_eur']);


if(isset($cgi['add_row'])) {
	if ($cgi['name']) {
		$query = "insert into $table set name = '".stripslashes($cgi[name])."', name_eng = '".stripslashes($cgi[name_eng])."', price_rub = '".$cgi[price_rub]."', price_eur = '".$cgi[price_eur]."', type_id = '".$cgi[type_id]."', ed_izm = '".$cgi[ed_izm]."'";
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}


if(isset($cgi['upd_row'])) {
	$sql = "update $table set name = '".stripslashes($cgi[name])."', name_eng = '".stripslashes($cgi[name_eng])."', price_rub = '".$cgi[price_rub]."', price_eur = '".$cgi[price_eur]."', type_id = '".$cgi[type_id]."', ed_izm = '".$cgi[ed_izm]."' where id=".$cgi['num_str'];
#  echo $sql;
	my_exec($sql);
}

if(isset($cgi['del_row'])) {
	$query = "delete from $table where id = ".$cgi['num_str'];
	my_exec($query);
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

#######################################################################33


function add($num_str) {
  global $body, $table, $lists;

  $name = $name_eng = $price_rub = $price_eur = $type_id = $ed_izm = '';
  $header = "Добавление информации";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
    $header = "Редактирование информации";
    $name = $row['name'];
    $name_eng = $row['name_eng'];
    $price_rub = $row['price_rub'];
    $price_eur = $row['price_eur'];
    $type_id = $row['type_id'];
    $ed_izm = $row['ed_izm'];
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }

	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=0 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  if ($num_str && $num_str < 100) {
    $body .= "<tr valign=top><td colspan=2><b>Название:</b>&nbsp;</td><td>$name<input type=hidden name=name value='".$name."'></td></tr>\n"; 
  } else {
    $body .= "<tr valign=top><td colspan=2><b>Тип услуги:</b>&nbsp;</td><td>".do_input('type_id',$type_id,'REF','razdel_price','name','','')."</td></tr>\n"; 
	  $body .= "<tr valign=top><td colspan=2><b>Название рус:</b>&nbsp;</td><td><input name=name value='".$name."' size=70></td></tr>\n";  
	  $body .= "<tr valign=top><td colspan=2><b>Название англ:</b>&nbsp;</td><td><input name=name_eng value='".$name_eng."' size=70></td></tr>\n";  
  }
	$body .= "<tr valign=top><td colspan=2><b>Ед. измер.:</b>&nbsp;</td><td>".do_combobox('ed_izm',$lists['arrEdIzm'],$ed_izm,'','')."</td></tr>\n"; 
	$body .= "<tr valign=top><td colspan=2><b>Цена за единицу, руб.:</b>&nbsp;</td><td><input name=price_rub value='".$price_rub."' size=10></td></tr>\n"; 
	$body .= "<tr valign=top><td colspan=2><b>Цена за единицу, &euro;:</b>&nbsp;</td><td><input name=price_eur value='".$price_eur."' size=10></td></tr>\n"; 
	$body .= "</table><BR>\n";
	$body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "</form></center>\n";
  return $body;
}

function show() {
  global $body, $table, $cgi, $lists;
	$arr_search = array(
		'name'=>array('Название','STR'),
		);
	$body = do_search($arr_search);
  $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
#  $body .= "<br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1><td width=50>&nbsp;</td>";
	$body .= "<td><b>Название</b></td>";
	$body .= "<td><b>Ед. изм</b></td>";
	$body .= "<td><b>Цена за единицу, руб.</b></td>";
	$body .= "<td><b>Цена за единицу, &euro;.</b></td>";
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
	$sql = "select * from $table $where order by id";
	#		echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top><td align=center nowrap>";
    if ($row['id'] > 100) {
      $body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
    }
    $body .= "<a href='$PHP_SELF?page=upd&num_str=".$row['id']."'><img src='/images/edit.gif' border=0></a>";
    $body .= "</td>";
    $body .= "<td>".$row['name']."&nbsp;</td><td align=center>".$lists['arrEdIzm'][$row['ed_izm']]."&nbsp;</td><td align=center>".$row['price_rub']."</td><td align=center>".$row['price_eur']."</td></tr>\n"; 
	}
	$body .= "</table>\n";
  return $body;
}

?>