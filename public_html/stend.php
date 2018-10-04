<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

if(isset($cgi['return_dogovor'])) {
  header("Location:http://$HTTP_HOST/dogovor.php?page=upd&num_str=".$cgi['num_str']);
	exit();
}

#print_arr($cgi);

$text_menu = $objAdmMenu->GetMenu($user_id, 'Договора с экспонентами');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "stend";

foreach ($_FILES as $k=>$v) {
  $name = $k;
  $name_name = $k."_name";
  $$name = $v[tmp_name];
  $$name_name = $v[name];
}
$path_to_file = "/web/sites/mosc.ru/sub_domains/exhibition/www/picture_stend/";



if(isset($cgi['upd_row'])) {
	if ($cgi['number']) {
    $sql_old = "select * from $table where dogovor_id=".$cgi['num_str'];
    $sth_old = my_exec($sql_old);
    $row_old = mysql_fetch_array($sth_old);
    $text_old = "";
    $text_old .= "Договор = ".$row_old[dogovor_id]."<br>";
    $text_old .= "Номер стенда = ".$row_old[number]."<br>";
    $text_old .= "Фриз = ".$row_old[friz]."<br>";

		$sql = "update $table set number = '".addslashes($cgi[number])."', friz = '".addslashes($cgi[friz])."', note = '".addslashes($cgi[note])."', is_ok = '".$cgi[is_ok]."' where dogovor_id=".$cgi['num_str'];
		my_exec($sql);
    if (isset($picture_name) && $picture_name) {
      $arr = split("\.",$picture_name);
      $ext = "." . strtolower($arr[count($arr)-1]);
      $pic = $path_to_file."pic_" . $cgi['num_str'] . $ext;
#					echo "$picture -> $pic<br>";
      if (copy($picture, $pic)) {
        $query = "UPDATE $table SET ext = '".$ext."' WHERE dogovor_id = " . $cgi['num_str'];
#						echo "picture -> ".$query."<br>";
        my_exec($query);
      } else {
        echo "Файл НЕ загружен, просьба сообщить об этом сбое администрации...";
      }
      @unlink ($picture);
    }

    $text_new = "Номер стенда = ".addslashes($cgi[number])."<br>Фриз = ".addslashes($cgi[friz]);
    $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='".$text_old."', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Обновлен стенд'";
    my_exec($query);

  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi[del_pic])) {
  $sql = "select * from $table where dogovor_id=".$cgi['num_str'];
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  if ($row[ext] != '---') {
    $pic = $path_to_file."pic_" . $cgi['num_str'] . $row[ext];
    @unlink ($pic);
  }
  $query = "update $table set ext = '---' where dogovor_id=".$cgi['num_str'];
  my_exec($query);
}


add($cgi['num_str']);      

include_once("usr_templ.php");


#######################################################################


function add($num_str) {
  global $filds, $body, $table, $lists;

  $sql = "select * from $table where dogovor_id='".$num_str."'";
  #		echo $sql;
  $sth = my_exec($sql);
  if (!mysql_num_rows($sth)) {
    $sql = "insert into $table set dogovor_id=$num_str";
    my_exec($sql);
    $row[ext] = '---';
  } else {
    $row = mysql_fetch_array($sth);
  }
  $header = "Редактирование стенда";
  $name_btn = "upd_row";
  $text_btn = "Обновить";
  
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
	$body .= "<tr valign=top><td><b>Номер стенда</b></td><td><textarea name='number' cols=50 rows=3>".stripslashes($row[number])."</textarea></td></tr>\n";
	$body .= "<tr valign=top><td><b>Фриз</b></td><td><textarea name='friz' cols=50 rows=3>".stripslashes($row[friz])."</textarea></td></tr>\n";
	$body .= "<tr valign=top><td><b>Примечания</b></td><td><textarea name='note' cols=50 rows=3>".stripslashes($row[note])."</textarea></td></tr>\n";
	$body .= "<tr valign=top><td><b>Стенд утвержден</b></td><td>".do_combobox('is_ok',$lists['arrCheck'],$row[is_ok],'','')."</td></tr>\n";
	$body .= "<tr valign=top><td><b>Планировка</b><br>Допустим jpg и pdf.<br>Мах размер: 2 Мб</td><td>";
  
  if ($row[ext] == '---') {
    $body .= "<input type=file name=picture>\n";
  } else {
    if ($row[ext] == '.pdf') {
      $body .= "<a href='/picture_stend/pic_".$num_str.$row[ext]."' target='_blank'><img src='/images/pdf.jpg' border=0 hspace=10></a>";
    } else {
      $body .= "<img src='/picture_stend/pic_".$num_str.$row[ext]."' border=0 hspace=10>";
    }
    $body .= "<input type=submit name=del_pic value='заменить'>\n";
  }
	$body .= "</td></tr>\n";

	$body .= "</table><BR>\n";
  $body .= "<input type=submit name=$name_btn value='  $text_btn  ' onClick='return check_form();'>\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
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
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}
?>