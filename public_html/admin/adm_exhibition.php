<?php

include_once("adm_menu.inc");
include_once("../../private/conf.inc");
include_once("../../private/func.inc");
if (!authenticateAdmin ($cookie_adm, $cookie_adm_passwd)) {
	header("Location:http://$HTTP_HOST/admin/index.php");
	exit();
}
$text_menu = $objAdmMenu->GetMenu('Проекты');
$table = "exhibition";
$cgi['search_date_1_def'] = '2000-01-01';
$cgi['search_date_2_def'] = '2010-01-01';

# print_arr($cgi);

$filds = array(
  'name' => array('Название','STR'),
  'name_full' => array('Название полное рус','STR'),
  'name_full_engl' => array('Название полное англ','STR'),
  'address' => array('Адрес проведения','STR'),
  'date_start' => array('Дата начала','DATE'),
  'date_stop' => array('Дата окончания','DATE'),
  'is_stop' => array('Проект закрыт','REF','arrCheck'),
);

$arr_1 = $arr_2 = $arr_3 = array();
reset($filds);

$cgi[date_start] = $cgi[date_start_y]."-".$cgi[date_start_m]."-".$cgi[date_start_d];
$cgi[date_stop] = $cgi[date_stop_y]."-".$cgi[date_stop_m]."-".$cgi[date_stop_d];

#print_arr($_FILES);
foreach ($_FILES as $k=>$v) {
  $name = $k;
  $name_name = $k."_name";
  $$name = $v[tmp_name];
  $$name_name = $v[name];
}
$path_to_file = "/web/sites/mosc.ru/sub_domains/exhibition/www/picture_plan/";

while (list($k,$v) = each ($filds)) {
	array_push ($arr_1,$k);
	array_push ($arr_2,"'".$cgi[$k]."'");
	array_push ($arr_3,$k."='".$cgi[$k]."'");
}

if(isset($cgi['add_row'])) {
	if ($cgi['name']) {
		$query = "insert into $table (".implode(",",$arr_1).") values (".implode(",",$arr_2).")";
		my_exec($query);
    /*
    $cgi['num_str'] = mysql_insert_id();
    if (isset($picture_name) && $picture_name) {
      $arr = split("\.",$picture_name);
      $ext = "." . strtolower($arr[count($arr)-1]);
      $pic = $path_to_file."pic_" . $cgi['num_str'] . $ext;
      if (copy($picture, $pic)) {
        $query = "UPDATE $table SET ext = '".$ext."' WHERE id = " . $cgi['num_str'];
        my_exec($query);
      } else {
        echo "Файл НЕ загружен, просьба сообщить об этом сбое администрации...";
      }
      @unlink ($picture);
    }
    */
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_row'])) {
	if ($cgi['name']) {
		$query = "update $table set ".implode(",",$arr_3)." where id=".$cgi['num_str'];
		my_exec($query);
  /*
    if (isset($picture_name) && $picture_name) {
      $arr = split("\.",$picture_name);
      $ext = "." . strtolower($arr[count($arr)-1]);
      $pic = $path_to_file."pic_" . $cgi['num_str'] . $ext;
      if (copy($picture, $pic)) {
        $query = "UPDATE $table SET ext = '".$ext."' WHERE id = " . $cgi['num_str'];
        my_exec($query);
      } else {
        echo "Файл НЕ загружен, просьба сообщить об этом сбое администрации...";
      }
      @unlink ($picture);
    }
    */
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_row'])) {
	$is_link=0;
  
	$query = "select * from dogovor where exhibition_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Договора с экспонентами, "; }
	$query = "select * from price where exhibition_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Прайс-лист, "; }
  
	if ($is_link) { 
    red("Запись не может быть удалена, т.к. существуют связанные с ней записи.<br>".substr($whereIt,0,-2)."<br>Сперва удалите их!");
  } else {
		$query = "delete from $table where id = ".$cgi['num_str'];
		my_exec($query);
	}
}
#print_arr($cgi);
if(isset($cgi['add_pic'])) {
  
  if (isset($picture_name) && $picture_name) {
    $sql = "insert into exhibition_plan set exhibition_id='".$cgi['num_str']."', name='".addslashes($cgi[name_pic])."'";
    my_exec($sql);
    $id = mysql_insert_id();
    $arr = split("\.",$picture_name);
    $ext = "." . strtolower($arr[count($arr)-1]);
    $pic = $path_to_file."pic_" . $id . $ext;
    if (copy($picture, $pic)) {
      $query = "UPDATE exhibition_plan SET ext = '".$ext."' WHERE id = " . $id;
      my_exec($query);
    } else {
      echo "Файл НЕ загружен, просьба сообщить об этом сбое администрации...";
    }
    @unlink ($picture);
  }
  $cgi['page'] = 'upd';
}



if(isset($cgi[del_pic])) {
  $sql = "select * from exhibition_plan where id=".$cgi[del_pic];
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  if ($row[ext] != '---') {
    $pic = $path_to_file."pic_" . $row[id] . $row[ext];
    @unlink ($pic);
  }
  $query = "delete from exhibition_plan where id=".$row[id];
  my_exec($query);
  $cgi['page'] = 'upd';
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
  global $filds, $body, $table;

	reset($filds);
	while (list($k,$v) = each($filds)) {
		$$k='';
	}
  $ext = '---';

  $header = "Добавление проекта";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
		$header = "Редактирование проекта";
		reset($filds);
		while (list($k,$v) = each($filds)) {
			$$k=$row[$k];
		}
    $ext = $row[ext];
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }
	$body = "<center><h4>$header</h4>";
  $body .= "<script language=JavaScript>
    function check_form(){
      if (document.f1.picture.value) {
        a = document.f1.picture.value.match(/.+\.(pdf|jpe?g|PDF|JPE?G)$/);
        if (!a) { alert('Неверный формат файла логотипа.\\nДопустимо только jpg и pdf'); document.f1.picture.focus(); return false;}
      }
      return true;
    }
    </script>
      \n";
  $body .= "<form method=post name=f1 enctype='multipart/form-data'>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	reset($filds);
	while (list($k,$v) = each($filds)) {
		$body .= "<tr valign=top><td><b>".$v[0]."</b></td><td>".do_input($k,$$k,$v[1],$v[2],'','','')."</td></tr>\n";
	}
	$body .= "<tr valign=top><td><b>Планировки</b></td><td>";

  if ($num_str != 0) {
    $body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr align=center bgcolor=e0e0e0><td>&nbsp;</td><td>Название</td><td>Изображение</td></tr>";
    $sql = "select * from exhibition_plan where exhibition_id=$num_str";
    $sth_pl = my_exec($sql);
    if (mysql_num_rows($sth_pl)) {
      
      while($row_pl = mysql_fetch_array($sth_pl)) {
        $body .= "<tr><td align=center><a href='$PHP_SELF?num_str=$num_str&del_pic=".$row_pl['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a></td><td>".stripslashes($row_pl['name'])."</td><td align=center>";
        if ($row_pl['ext']=='---') {
          $body .= '&nbsp;';
        } else {
          if ($row_pl['ext']=='.pdf') {
            $body .= "<a href='/picture_plan/pic_".$row_pl[id].$row_pl[ext]."' target='_blank'><img src='/images/pdf.jpg' border=0 width=24 hspace=10></a>";
          } else {
            $body .= "<a href='#' onClick=\"return w_o('/show_pic_2.php?id=".$row_pl[id]."',300,300);\"><img src='/images/image.png' border=0 hspace=8></a>";
          }
        }
        $body .= "</td></tr>";
      }
      
    }
    $body .= "<tr><td><input type=submit name=add_pic value='добавить' onClick='return check_form();'></td><td><input name=name_pic size=50></td><td><input type=file name=picture></td></tr>";
    $body .= "</table>";
  } else {
    $body .= "Сперва нужно сохранить информацию о проекте...";
  }

  
 /* 
  if ($ext == '---') {
    $body .= "<input type=file name=picture>\n";
  } else {
    $body .= "<img src='/picture_plan/pic_".$num_str.$ext."' border=0 hspace=10>";
    $body .= "<input type=submit name=del_pic value='заменить'>\n";
  }
  */
	$body .= "&nbsp;</td></tr>\n";
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

	$body .= "<script language=javascript>\n";
  $body .= "function do_price(id) { w_o('adm_price.php?exhib='+id,700,600,',scrollbars=1,resizable=1'); return false;}\n";
  $body .= "function do_service(id) { w_o('adm_service.php?exhib='+id,700,600,',scrollbars=1,resizable=1'); return false;}\n";
  $body .= "</script>";

  $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr align=center class=tr1><td width=50>&nbsp;</td>";
	$body .= "<td><b>Название</b></td>";
	$body .= "<td><b>Начало</b></td>";
	$body .= "<td><b>Конец</b></td>";
  $body .= "<td><b>Прайс-лист</b></td>";
  $body .= "<td><b>Стандартные услуги</b></td>";
  $body .= "<td><b>Планировка</b></td>";
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

	$sql = "select * from $table $where order by name";
	#		echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top align=center><td nowrap><a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> <a href='$PHP_SELF?page=upd&num_str=".$row['id']."'><img src='/images/edit.gif' border=0></a></td>";
    $body .= "<td>".$row[name]."&nbsp;</td>";
		$body .= "<td>".mysql2date2($row[date_start])."&nbsp;</td>";
		$body .= "<td>".mysql2date2($row[date_stop])."&nbsp;</td>";
    $body .= "<td><a href='adm_price.php?exhib=".$row['id']."' onClick='do_price(".$row['id']."); return false;'>&gt;&gt;&gt;</a></td>";
    $body .= "<td><a href='adm_service.php?exhib=".$row['id']."' onClick='do_service(".$row['id']."); return false;'>&gt;&gt;&gt;</a></td>";
    $body .= "<td>";
    $sql = "select * from exhibition_plan where exhibition_id='".$row['id']."'";
    $sth_pl = my_exec($sql);

    while($row_pl = mysql_fetch_array($sth_pl)) {
      if ($row_pl['ext']=='---') {
        $body .= '&nbsp;';
      } else {
        if ($row_pl['ext']=='.pdf') {
          $body .= "<a href='/picture_plan/pic_".$row_pl[id].$row_pl[ext]."' target='_blank'><img src='/images/pdf.jpg' border=0 width=24 hspace=10></a>";
        } else {
          $body .= "<a href='#' onClick=\"return w_o('/show_pic_2.php?id=".$row_pl[id]."',300,300);\"><img src='/images/image.png' border=0 hspace=8></a>";
        }
      }
    }

    $body .= "&nbsp;</td>";
		$body .= "</tr>\n"; 
	}
	$body .= "</table>\n";
  return $body;
}

?>