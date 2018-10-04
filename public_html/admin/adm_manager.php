<?php
/* adm_manager.php */

include_once("adm_menu.inc");
include_once("../../private/conf.inc");
include_once("../../private/func.inc");
if (!authenticateAdmin ($cookie_adm, $cookie_adm_passwd)) {
	header("Location:http://$HTTP_HOST/admin/index.php");
	exit();
}
$text_menu = $objAdmMenu->GetMenu('Сотрудники');
$table = "manager";
#	$date = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

$arr_1 = $arr_2 = $arr_3 = array();
$arr = array('name','phone','email','type','pass');


#,'serv_comp','serv_prih','serv_rash','serv_publ','serv_sten');
# print_arr($arr);

while (list(,$k) = each ($arr)) {
	array_push ($arr_1,$k);
	array_push ($arr_2,"'".$cgi[$k]."'");
	array_push ($arr_3,$k."='".$cgi[$k]."'");
}

if(isset($cgi['add_row'])) {
	if ($cgi['name'] && $cgi['pass']) {
		$query = "insert into $table (".implode(",",$arr_1).") values (".implode(",",$arr_2).")";
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_row'])) {
	if ($cgi['name'] && $cgi['pass']) {
		$query = "update $table set ".implode(",",$arr_3)." where id=".$cgi['num_str'];
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_row'])) {
	$is_link=0;
  
	$query = "select * from dogovor where manager_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Договора с экспонентами, "; }
	$query = "select * from company where manager_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Экспоненты, "; }
  
	if ($is_link) { 
    red("Запись не может быть удалена, т.к. существуют связанные с ней записи.<br>".substr($whereIt,0,-2)."<br>Сперва удалите их!");
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

#######################################################################33


function add($num_str) {
  global $body, $table, $lists;
  $name = $phone = $email = $type = $pass = '';

  $header = "Добавление информации о сотруднике";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
		$header = "Редактирование информации о сотруднике";
    $name=$row['name'];
    $phone=$row['phone'];
    $email=$row['email'];
    $type=$row['type'];
    $pass=$row['pass'];
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }
	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=0 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	$body .= "<tr valign=top><td colspan=2><b>Фамилия Имя Отчество:</b>&nbsp;&nbsp;<input name=name value='$name' size=69></td></tr>\n"; 
	$body .= "<tr valign=top><td><b>Телефон:</b>&nbsp;&nbsp;<input name=phone value='$phone' size=27></td><td align=right><b>Тип:</b>&nbsp;&nbsp;".do_combobox('type',$lists[arrTypeMan],$type,'',0)."</td></tr>\n"; 
	$body .= "<tr valign=top><td><b>E-mail:</b>&nbsp;&nbsp;<input name=email value='$email' size=30></td><td align=right><b>Пароль:</b>&nbsp;&nbsp;<input name=pass value='$pass' size=25></td></tr>\n";	 
	$body .= "</table><BR>\n";
	$body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "</form></center>\n";
  return $body;
}

function show() {
  global $body, $table, $lists, $cgi;
	$arr_search = array(
		'name'=>array('ФИО','STR'),
		'type'=>array('Тип','REF','arrTypeMan'),
		);
	$body = do_search($arr_search);
  $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1><td width=50>&nbsp;</td>";
	$body .= "<td><b>ФИО</b></td>";
  $body .= "<td><b>Тип</b></td>";
  $body .= "<td><b>Телефон</b></td>";
  $body .= "<td><b>E-mail</b></td>";
  $body .= "<td><b>Пароль</b></td>";
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
		$body .= "<tr valign=top><td align=center nowrap><a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> <a href='$PHP_SELF?page=upd&num_str=".$row['id']."'><img src='/images/edit.gif' border=0></a></td>";
    $body .= "<td>".$row['name']."&nbsp;</td><td>".$lists[arrTypeMan][$row['type']]."&nbsp;</td><td>".$row['phone']."&nbsp;</td><td>".$row['email']."&nbsp;</td><td>".$row['pass']."&nbsp;</td></tr>\n"; 
	}
	$body .= "</table>\n";
  return $body;
}

?>