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
/*
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
  $query = "insert into statistic set user_id=$user_id, date=now(), type='insert', text_new='".$text_new."', sql='".addslashes($sql)."', comment='Добавлена задача'";
  my_exec($query);
}

foreach ($cgi as $k=>$v) {
  if ($k == 'set_item') {
#    echo "$k = $v";
    $sql = "update $table set status = '1' where id = '".$v."'";
    my_exec($sql);

    $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='status = 0', text_new='status = 1', sql='".addslashes($sql)."', comment='Выполнена задача'";
    my_exec($query);

#    $sql = "insert into task_stat set datetime = now(), manager_id = '".$arrUser[0]."', status_old = '0', status_new = '1', task_id = '".$v."'";
#    my_exec($sql);
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
	$is_link=0;
	if ($is_link) { 
    $error=red2("Запись не может быть удалена, т.к. существуют связанные с ней записи. <br>".substr($whereIt,0,-2)."<br>Сперва удалите их!");
  } else {
		$query = "delete from $table where id = ".$cgi['num_str'];
		my_exec($query);
	}
}
*/

show(); 

include_once("usr_templ.php");


#######################################################################


function show() {
  global $body, $table, $cgi, $arrUser, $lists, $this_project;
#  print_arr($arrUser);

  if (!$cgi["btn_search"]) {
		$cgi['search_manager_id'] = $arrUser[0];
  }

	$body = do_search_here();

	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  if ($arrUser[1] == 'a') {
    if($cgi[oldOrdCol] == 'manager'){
      if($cgi[ordDesc] == ' desc '){
        $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
      }else{
        $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
      }
    } else {
      $str = "";
    }
    $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='manager'; document.form_search.submit();return false;\"><b><font color=white>Менеджер</font></b></a>&nbsp;&nbsp;$str</td>";
  }
	$body .= "<td><b>Клиент</b></td>";
	$body .= "<td><b>Цель контакта</b></td>";
	$body .= "<td><b>Дата контакта</b></td>";
  $body .= "</tr>";
  $body .= "<form method=post>\n";

	if($cgi[oldOrdCol]){
		$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
  } else {
	  $order = " order by date";
  }
/*
  if ($arrUser[1] == 'm') {
    $where .= " and task.manager_id=".$arrUser[0];
  } 
*/

  if ($cgi['search_client_id']) {
		$where .= " and company.id = ".$cgi['search_client_id'];
  }
  if ($cgi['search_manager_id']) {
		$where .= " and company.manager_id = ".$cgi['search_manager_id'];
  }
  if ($cgi['search_status'] == 0) {
		$where .= " and (cpp.text is null or cpp.text = '') ";
	} else {
		$where .= " and cpp.text is not null and cpp.text <> '' ";
  }

  if ($cgi["btn_all"]) {
		$where = "";
	}

#  $where_main = "";
#  if ($cgi['search_exhibition_id']) {
#		$where_main .= " and $table.exhibition_id=".$cgi['search_exhibition_id'];
#	}

	#$sql = "select task.*, company.name as c_name, manager.name as m_name from task, company, manager where manager.id = task.manager_id and task.client_id = company.id $where $order";

	$sql = "select company.id as c_id, company.name as c_name, cpp.date, cpp.task, cpp.text, manager.name as manager from company_project cp, company_project_plan cpp, company, manager where manager.id=company.manager_id and company.id=cp.company_id and cpp.company_project_id=cp.id and cp.project_id = '".$this_project."' $where $order";

#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
    if (!$row[text] && ($row[date] < date("Y-m-d"))) {
      $bg = " bgcolor=#FF6699";
    } else {
      $bg = "";
    }
		$body .= "<tr valign=top $bg>";
    if ($arrUser[1] == 'a') {
      $body .= "<td>".$row[manager]."&nbsp;</td>"; # ????
    }
    $body .= "<td><a href='/company.php?page=upd&num_str=".$row[c_id]."'>".stripslashes($row[c_name])."</a>&nbsp;</td><td>".nl2br(stripslashes($row[task]))."&nbsp;</td><td>".mysql2date2($row[date])."&nbsp;</td>\n"; 
	}
  $body .= "</tr>";
  $body .= "</form>\n";
	$body .= "</table>\n";

  return $body;
}

function do_search_here() {
	global $cgi, $table, $arrUser, $arrManager, $lists, $this_project;

  if ($arrUser[1] == 'm') {
    $sql = "select company.id, company.name as c_name, dogovor.number from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' and company.manager_id='".$arrUser[0]."' order by company.name, dogovor.number";
  } else {
    $sql = "select company.id, company.name as c_name, dogovor.number from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' order by company.name, dogovor.number";
  }
  $sth = my_exec($sql);
  $arrCompany = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrCompany[$row[id]] = stripslashes($row[c_name])." (договор № ".$row[number].")";
  }

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
    $cgi["search_client_id"] = "";
    $cgi["search_manager_id"] = "";
  }

  $cont .= "<tr><td><b>Статус</b>&nbsp;&nbsp;</td><td>".do_combobox('search_status',$lists[arrTaskStatus],$cgi["search_status"],'','')."</td>";
  $cont .= "<tr><td><b>Менеджер</b>&nbsp;&nbsp;</td><td>".do_combobox('search_manager_id',$arrManager,$cgi["search_manager_id"],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Компания</b>&nbsp;&nbsp;</td><td>".do_combobox('search_client_id',$arrCompany,$cgi["search_client_id"],'',1)."</td></tr>\n";

  $cont .= "<tr><td colspan=2 align=center>&nbsp;&nbsp;<input type=submit name=btn_search value='Поиск'></td></tr>";
  

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