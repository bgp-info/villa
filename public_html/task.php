<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

#print_arr($cgi);

$sql = "select id, name from manager where type='m' order by name";
$sth = my_exec($sql);
$arrManager = array();
while ($row = mysql_fetch_array($sth)) {
	$arrManager[$row[id]] = $row[name];
}


$text_menu = $objAdmMenu->GetMenu($user_id, 'Задачи');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "task";

if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

if(isset($cgi['add_row'])) {
	$sql = "insert into $table set manager_id = '".$arrUser[0]."', client_id = '".$cgi[client_id]."', date = '".$cgi[date]."', text = '".addslashes($cgi[text])."'";
	my_exec($sql);

  $text_new = "";
  $text_new .= "Менеджер = ".$arrUser[2]."<br>";
  $text_new .= "Экспонент = ".$cgi[client_id]."<br>";
  $text_new .= "Дата = ".$cgi[date]."<br>";
  $text_new .= "Текст = ".addslashes($cgi[text])."<br>";
  $query = "insert into statistic set user_id=$user_id, date=now(), type='insert', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Добавлена задача'";
  my_exec($query);
}

foreach ($cgi as $k=>$v) {
  if ($k == 'set_item') {
#    echo "$k = $v";
    $sql = "update $table set status = '1' where id = '".$v."'";
    my_exec($sql);

    $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='status = 0', text_new='status = 1', text_sql='".addslashes($sql)."', comment='Выполнена задача'";
    my_exec($query);

#    $sql = "insert into task_stat set datetime = now(), manager_id = '".$arrUser[0]."', status_old = '0', status_new = '1', task_id = '".$v."'";
#    my_exec($sql);
  }
}

/*
if(isset($cgi['upd_row'])) {
	if ($cgi['name']) {
		$query = "update $table set ".implode(",",$arr_3)." where id=".$cgi['num_str'];
		my_exec($query);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_row'])) {
	$is_link=0;
	if ($is_link) { 
    $error=red2("Запись не может быть удалена, т.к. существуют связанные с ней записи. <br>".substr($whereIt,0,-2)."<br>Сперва удалите их!");
  } else {
		$query = "delete from $table where id = ".$cgi['num_str'];
		my_exec($query);
	}
}
*/

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
include_once("usr_templ.php");


#######################################################################


function add($num_str) {
  global $filds, $body, $table, $arrUser;

  $header = "Добавление задачи";
  $name_btn = "add_row";
  $text_btn = "Добавить";

	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";

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
  $body .= "<tr valign=top><td><b>Компания</b></td><td>".do_combobox('client_id',$arr,'','',0)."</td></tr>\n";
  $body .= "<tr valign=top><td><b>Дата</b></td><td>".do_input('date','','DATE','','','','')."</td></tr>\n";
  $body .= "<tr valign=top><td><b>Операция</b></td><td><textarea name='text' cols=60 rows=6></textarea></td></tr>\n";


	$body .= "</table><BR>\n";
  $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "</form></center>\n";
  return $body;
}




function show() {
  global $body, $table, $cgi, $arrUser, $lists;
#  print_arr($arrUser);

	$body = do_search_here();

  if ($arrUser[1] == 'm') {
    $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
  } else {
    $body .= "<br><br>";
  }
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  if ($arrUser[1] == 'a') {
    $body .= "<td><b>Менеджер</b></td>";
  }
	$body .= "<td><b>Название</b></td>";
	$body .= "<td><b>Операция</b></td>";
	$body .= "<td><b>Дата </b></td>";
	$body .= "<td><b>Выполнено</b></td>";
  $body .= "</tr>";
  $body .= "<form method=post>\n";

	$order = " order by date";

  if ($arrUser[1] == 'm') {
    $where .= " and task.manager_id=".$arrUser[0];
  } 

  if ($cgi['search_status'] == 1) {
		$where .= " and task.status=1 ";
	} else {
		$where .= " and task.status=0 ";
  }

  if ($cgi["btn_all"]) {
		$where = "";
	}

#  $where_main = "";
#  if ($cgi['search_exhibition_id']) {
#		$where_main .= " and $table.exhibition_id=".$cgi['search_exhibition_id'];
#	}

	$sql = "select task.*, company.name as c_name, manager.name as m_name from task, company, manager where manager.id = task.manager_id and task.client_id = company.id $where $order";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
    if ($row[status] == 0 && ($row[date] < date("Y-m-d"))) {
      $bg = " bgcolor=#FF6699";
    } else {
      $bg = "";
    }
		$body .= "<tr valign=top $bg>";
    if ($arrUser[1] == 'a') {
      $body .= "<td>".$row[m_name]."&nbsp;</td>";
    }
    $body .= "<td>".$row[c_name]."&nbsp;</td><td>".nl2br(stripslashes($row[text]))."&nbsp;</td><td>".mysql2date2($row[date])."&nbsp;</td>\n"; 

    if ($arrUser[1] == 'm' && $row[status] == 0) {
      $body .= "<td align=center><input type=checkbox name=set_item value=".$row[id]." onClick=submit();>&nbsp;</td>";
    } else {
      $body .= "<td align=center>".$lists[arrTaskStatus][$row[status]]."</td>";
    }

	}
  $body .= "</tr>";
  $body .= "</form>\n";
	$body .= "</table>\n";

  return $body;
}

function do_search_here() {
	global $cgi, $table, $arrUser, $lists;
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
    $cgi["search_status"] = "";
  }

  $cont .= "<tr><td><b>Статус</b>&nbsp;&nbsp;</td><td>".do_combobox('search_status',$lists[arrTaskStatus],$cgi["search_status"],'','')."</td>";
  $cont .= "<td>&nbsp;&nbsp;<input type=submit name=btn_search value='Поиск'></td>";
  
  $cont .= "</tr>\n";

	$cont .= "</table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
#	$cont .= "<input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
#	$cont .= "<input type=submit name=btn_search value='Поиск'>";
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
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}
?>