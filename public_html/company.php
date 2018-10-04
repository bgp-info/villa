<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}
/*
if ($cgi['work_type']) {
  $temp = ":";
  foreach($cgi['work_type'] as $k=>$v) {
    $temp .= $v.":";
  }
  $cgi['work_type'] = $temp;
}
#print_arr($cgi);
*/
$sql = "select id, name from city";
$sth = my_exec($sql);
$arrCity = array();
while ($row = mysql_fetch_array($sth)) {
	$arrCity[$row[id]] = $row[name];
}
$sql = "select id, name from manager where type='m' order by name";
$sth = my_exec($sql);
$arrManager = array();
while ($row = mysql_fetch_array($sth)) {
	$arrManager[$row[id]] = $row[name];
}
$sql = "select id, name from exhibition order by id desc";
$sth = my_exec($sql);
$arrProject = array();
while ($row = mysql_fetch_array($sth)) {
	$arrProject[$row[id]] = $row[name];
}
$sql = "select id, name from work_type order by name";
$sth = my_exec($sql);
$arrWorkType = array();
while ($row = mysql_fetch_array($sth)) {
	$arrWorkType[$row[id]] = $row[name];
}

$text_menu = $objAdmMenu->GetMenu($user_id, 'Экспоненты');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "company";

$filds = array(

#  'name'=>array('Референс','STR'),
  'inn'=>array('ИНН','STR','','',40,10),
  'kpp'=>array('КПП','STR','','',40,9),
  'ras_schet'=>array('Рас. счет','STR','','',40,20),
  'kor_schet'=>array('Кор. счет','STR','','',40,20),
  'bank'=>array('Банк','STR','','',40),
  'bik'=>array('БИК','STR','','',40,9),

  'name'=>array('Название краткое','STR'),
  'name_full'=>array('Название полное','STR'),
  'name_engl'=>array('Название англ.','STR'),
  'city_id'=>array('Регион','REF','city','name'),
  'address'=>array('Адрес юр. рус.','STR'),
  'address_engl'=>array('Адрес юр. англ.','STR'),
  'address_fakt'=>array('Адрес факт.','STR'),

  'phone'=>array('Телефн','STR','','',40),
  'fax'=>array('Факс','STR','','',40),
  'email'=>array('E-mail','STR','','',40),
  'www'=>array('WWW','STR','','',40),

  'ruk_im'=>array('Руководитель','STR'),
  'dolg_ruk_im'=>array('Должность руководителя','STR'),

  'contakt_fio'=>array('Контактное лицо','STR'),
  'contakt'=>array('Координаты','STR'),
  'work_type'=>array('Виды деятельности','STR'),
  'is_invite'=>array('Отправлено приглашение','REF','arrCheck'),
  'manager_id'=>array('Куратор','REF','manager','name'),

  'note'=>array('Примечание','TEXT'),

);
reset($filds);

$arr_1 = $arr_2 = $arr_3 = $arr_4 = array();
reset($filds);
while (list($k,$v) = each ($filds)) {
  if ($k == 'work_type') continue;
	array_push ($arr_1,$k);
	array_push ($arr_2,"'".trim(addslashes($cgi[$k]))."'");
	array_push ($arr_3,$k."='".trim(addslashes($cgi[$k]))."'");
	array_push ($arr_4,$filds[$k][0]."=".trim(addslashes($cgi[$k])));
}

if(isset($cgi['add_project'])) {
	if ($cgi['new_project']) {
    #print_arr($cgi);
		$sql = "insert into company_project set company_id = ".$cgi[num_str].", project_id = ".$cgi['new_project'].", status = 1";
    #echo $sql;
		my_exec($sql);
    $cgi[page] = 'upd';
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_project'])) {
  $sql = "delete from company_project where id = ".$cgi['del_project'];
  my_exec($sql);
  $cgi[page] = 'upd';
}

if(isset($cgi['add_row'])) {
	if ($cgi['name']) {
		$sql = "insert into $table (".implode(",",$arr_1).", xml_flg) values (".implode(",",$arr_2).", 'Y')";
		my_exec($sql);
		$query = "insert into statistic set user_id=$user_id, date=now(), type='insert', text_new='".implode("<br>",$arr_4)."', text_sql='".addslashes($sql)."', comment='Добавлен экспонент'";
		my_exec($query);
    $company_id = mysql_insert_id();
    make_xml("company",$company_id);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_row'])) {
	if ($cgi['name']) {
    $sql_old = "select * from $table where id=".$cgi['num_str'];
    $sth_old = my_exec($sql_old);
    $row_old = mysql_fetch_array($sth_old);
    $text_old = "";
    foreach($row_old as $k=>$v) {
      $text_old .= ($filds[$k][0]?$filds[$k][0]."=".$v."<br>":"");
    }

		$sql = "update $table set ".implode(",",$arr_3).", xml_flg='Y' where id=".$cgi['num_str'];
    //echo $sql;
		my_exec($sql);
		$query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='".$text_old."', text_new='".implode("<br>",$arr_4)."', text_sql='".addslashes($sql)."', comment='Обновлен экспонент'";
		my_exec($query);
    make_xml("company",$cgi['num_str']);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['del_row'])) {
	$is_link=0;
	$query = "select * from dogovor where client_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Договора с экспонентами, "; }
  /*
	$query = "select * from operator where company_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Операторы, "; }
	$query = "select * from postav where company_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Поставщики, "; }
	$query = "select * from prihod where client_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Договора с экспонентами, "; }
	$query = "select * from rashod where client_id = ".$cgi['num_str'];
	$result = my_exec($query);
	if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Договора с исполнителями, "; }
  */
	if ($is_link) { 
    $error=red2("Запись не может быть удалена, т.к. существуют связанные с ней записи. <br>".substr($whereIt,0,-2)."<br>Сперва удалите их!");
  } else {
    $sql_old = "select * from $table where id=".$cgi['num_str'];
    $sth_old = my_exec($sql_old);
    $row_old = mysql_fetch_array($sth_old);
    $text_old = "";
    foreach($row_old as $k=>$v) {
      $text_old .= ($filds[$k][0]?$filds[$k][0]."=".$v."<br>":"");
    }
		$sql = "delete from $table where id = ".$cgi['num_str'];
		my_exec($sql);
		$query = "insert into statistic set user_id=$user_id, date=now(), type='delete', text_old='".$text_old."', text_sql='".addslashes($sql)."', comment='Удален экспонент'";
		my_exec($query);
    make_xml();
	}
}

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
  global $filds, $body, $table, $arrUser, $arrCity, $arrManager, $lists;
	reset($filds);
	while (list($k,$v) = each($filds)) {
		$$k='';
	}
	$city_id=1;
  if ($arrUser[1] == 'm') {
	  $manager_id = $arrUser[0];
  }

  $header = "Добавление экспонента";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
		$header = "Редактирование экспонента &nbsp;&nbsp;&nbsp;(<a href='print_company.php?id=$num_str' target='_blank'>Версия для печати</a>)";
		reset($filds);
		while (list($k,$v) = each($filds)) {
			$$k=stripslashes($row[$k]);
#      echo $kk." = ".$row[$k];
		}
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }
	$body = "<center><h4>$header</h4>";

  $body .= "<script language=javascript>\n";
  $body .= "function go_www() {\n";
  $body .= "  //alert('ffff');\n";
  $body .= "  var url = document.f1.www.value;\n";
  $body .= "  if (url.indexOf('http://') == -1) {\n";
  $body .= "    url = 'http://'+url;\n";
  $body .= "  }\n";
  $body .= "  window.open(url,'');\n";
  $body .= "  return false;\n";
  $body .= "}\n";
  $body .= "function go_email() {\n";;
  $body .= "  var mail = document.f1.email.value;\n";
  $body .= "  if (mail.indexOf('mailto:') == -1) {\n";
  $body .= "    mail = 'mailto:'+mail;\n";
  $body .= "  }\n";
  $body .= "  window.open(mail,'');\n";
  $body .= "  return false;\n";
  $body .= "}\n";
  $body .= "</script>\n";

  $body .= "<form name=f1 method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";

  if ($num_str) {
    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr align=center bgcolor=d0d0d0><td colspan=4><b>Участие в проектах</b></td></tr>";

    $body .= "<tr align=center bgcolor=efefef><td>&nbsp;</td><td>Название проекта</td><td>Статус</td><td>Планировщик</td></tr>";
    $sql = "select cp.*, exhibition.name, exhibition.is_stop from company_project cp, exhibition where exhibition.id = cp.project_id and cp.company_id=$num_str";
    $sth_pr = my_exec($sql);
    if (mysql_num_rows($sth_pr)) {
      while($row_pr = mysql_fetch_array($sth_pr)) {
        if ($row_pr['is_stop'] == 'N') {
          $text_link = "планировщик";
        } else {
          $text_link = "архив переговоров";
        }
        $body .= "<tr><td align=center><a href='$PHP_SELF?num_str=$num_str&del_project=".$row_pr['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a></td><td>".stripslashes($row_pr['name'])."</td><td>".$lists[arrCompanyProjectStatus][$row_pr['status']]."</td><td><a href=# onclick=\"return w_o('/set_plan.php?cp_id=".$row_pr['id']."',800,800,1);\">$text_link</a></td></tr>";
      }
      #<td align=center>".($row_pl['ext']=='---'?'&nbsp;':"<a href='#' onClick=\"return w_o('/show_pic_2.php?id=".$row_pl[id]."',300,300);\"><img src='/images/image.png' border=0 hspace=8></a>")."</td>
    }
    $sql = "select id, name from exhibition order by id desc";
    $sth_pr = my_exec($sql);
    $arrPr = array();
    while ($row_pr = mysql_fetch_array($sth_pr)) {
      $arrPr[$row_pr[id]] = $row_pr[name];
    }
    $body .= "<tr><td><input type=submit name=add_project value='Добавить'></td><td colspan=3>".do_combobox('new_project',$arrPr,'','','')."</td></tr>";
    $body .= "</table><br>";
  } else {
    $body .= "После добавления клиента вы сможете добавить проекты, в которых он участвует...<br><br>";
  }


	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	reset($filds);
	while (list($k,$v) = each($filds)) {
    if ($k == 'manager_id') {
      $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_combobox($k,$arrManager,$$k,'','')."</td></tr>\n";
    } elseif ($k == 'name') {
      $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input_here($k,$$k,$v[1],$v[2],$v[3],$v[4],$v[5])."<br><font color=red>Хочу обратить Ваше внимание, что это поле не является дублем Полного названия. Сюда следует забивать именно КОРОТКОЕ название. Это сделано не по прихоти разработчика, а чтобы сайт Ваш не разъезжался!!!</font></td></tr>\n";
    } elseif ($k == 'work_type') {
      if ($num_str) {
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input_here('work_type[]',$$k,'MY2','work_type','name',$v[4],$v[5])."<br><a href=# onclick=\"return w_o('set_wt.php?num_str=$num_str',500,500);\">изменить >>></a></td></tr>\n";
        #$body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input_here('work_type[]',$$k,'MY','work_type','name',$v[4],$v[5])."<br><font color=red>Выбор нескольких видов деятельности осуществляется с нажатой клавишей <b>Ctrl</b></font></td></tr>\n";
      } else {
        $body .= "<tr valign=top><td><b>$v[0]</b></td><td>После добавления экспонента вы сможете определить это поле!</td></tr>\n";
      }
    } elseif ($k == 'www') {
      $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input_here($k,$$k,$v[1],$v[2],$v[3],$v[4],$v[5])."&nbsp;&nbsp;&nbsp;<a href=# onClick=\"return go_www();\">перейти на сайт</a></td></tr>\n";
    } elseif ($k == 'email') {
      $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input_here($k,$$k,$v[1],$v[2],$v[3],$v[4],$v[5])."&nbsp;&nbsp;&nbsp;<a href=# onClick=\"return go_email();\">написать письмо</a></td></tr>\n";
    } else {
      $body .= "<tr valign=top><td><b>$v[0]</b></td><td>".do_input_here($k,$$k,$v[1],$v[2],$v[3],$v[4],$v[5])."</td></tr>\n";
    }
	}
	$body .= "</table><BR>\n";
  $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "</form></center>\n";
  return $body;
}

function info($num_str) {
  global $filds, $body, $table, $arrUser, $arrCity, $arrManager;

  $sql = "select * from $table where id=$num_str";
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  $header = "Информация по экспоненту &nbsp;&nbsp;&nbsp;(<a href='print_company.php?id=$num_str' target='_blank'>Версия для печати</a>)";
  reset($filds);
  while (list($k,$v) = each($filds)) {
    $$k=stripslashes($row[$k]);
#      echo $kk." = ".$row[$k];
  }
  $name_btn = "upd_row";
  $text_btn = "Обновить";

	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	reset($filds);
	while (list($k,$v) = each($filds)) {
    if ($k == 'city_id') {
      $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$arrCity[$$k]."&nbsp;</td></tr>\n";
    } elseif ($k == 'manager_id') {
      $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$arrManager[$$k]."&nbsp;</td></tr>\n";
    } elseif ($k == 'work_type') {
      if (strlen($$k) > 2) {
        $sql = "select id, name from work_type order by name";
        $sth_wtn = my_exec($sql);
        while($row_wtn = mysql_fetch_array($sth_wtn)) {
          $arrWTN[$row_wtn['id']] = stripslashes($row_wtn['name']);
        }
        $arrTemp = split(":",substr($$k,1,-1));
        $arrWT = array();
        foreach($arrTemp as $kwt=>$vwt) {
          $arrWT[] = $arrWTN[$vwt];
        }
        $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".join(", ",$arrWT)."&nbsp;</td></tr>\n";
      }
    } else {
      $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$$k."&nbsp;</td></tr>\n";
    }
	}
	$body .= "</table><BR>\n";
  $body .= "<input type=button onClick=history.back() value='  Вернуться к списку  '>\n";
  $body .= "</form></center>\n";
  return $body;
}


function show() {
  global $body, $table, $cgi, $arrUser;
#  print_arr($arrUser);
/*
	$arr_search = array(
    'project_id'=>array('Проект','REF','exhibition','name',1),
    'manager_id'=>array('Менеджер','REF','manager','name',1),
		'name'=>array('Название','STR'),
		);
*/
	$body = do_search_here($arr_search);
  if ($arrUser[1] == 'm' || $arrUser[1] == 'a') {
    $body .= "<a href='$PHP_SELF?page=add'>Добавить запись</a><br><br>";
  } else {
    $body .= "<br><br>";
  }
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  $body .= "<td width=30><b>№</b></td>";
  if ($arrUser[1] == 'm' || $arrUser[1] == 'a') {
	  $body .= "<td width=30>&nbsp;</td>";
  }
  
#	$body .= "<td><b>Название</b></td>";
#	$body .= "<td><b>Регион</b></td>";

  if($cgi[oldOrdCol] == 'name'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='name'; document.form_search.submit();return false;\"><b><font color=white>Название</font></b></a>&nbsp;&nbsp;$str</td>";

  if($cgi[oldOrdCol] == 'city_id'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='city_id'; document.form_search.submit();return false;\"><b><font color=white>Регион</font></b></a>&nbsp;&nbsp;$str</td>";


	$body .= "<td><b>Контактное лицо</b></td>";
	$body .= "<td><b>Координаты</b></td>";
  $body .= "</tr>";
/*
	reset($arr_search);
	while (list($k,$v) = each($arr_search)) {
#		echo "$k = $v[1]<br>";
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
*/
  if ($cgi["search_project_id"]) {
    $arrWhere[] = "company_project.project_id = '".$cgi["search_project_id"]."'";
    $arrWhere[] = "company_project.company_id = ".$table.".id";
    $addFrom .= ", company_project";
    $cp_link = 'Y';
  }
  if ($cgi["search_status_id"]) {
    $arrWhere[] = "company_project.status = '".$cgi["search_status_id"]."'";
    if (!$cp_link) {
      $arrWhere[] = "company_project.company_id = ".$table.".id";
      $addFrom .= ", company_project";
      $cp_link = 'Y';
    }
  }
  if ($cgi["search_manager_id"]) {
    $arrWhere[] = $table.".manager_id='".$cgi["search_manager_id"]."'";
  }
  if ($cgi["search_invite"]) {
    $arrWhere[] = $table.".is_invite='".$cgi["search_invite"]."'";
  }
  if ($cgi["search_work_type"]) {
    $arrWhere[] = $table.".work_type like '%:".$cgi["search_work_type"].":%'";
  }
  if ($cgi["search_name"]) {
    $arrWhere[] = $table.".name like '%".$cgi["search_name"]."%'";
  }

	if($cgi[oldOrdCol]){
		if ($cgi[oldOrdCol] == 'name') {
			$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
		}	elseif ($cgi[oldOrdCol] == 'city_id') {
			$order = " order by city_name ".$cgi[ordDesc];
		}
  } else {
    $order = " order by $table.name";
  }
  if (count($arrWhere)) {
		$where = "and ".join(" and ",$arrWhere);
	}
  if ($cgi["btn_all"]) {
		$where = "";
	}
  $i = 0;
	$sql = "select $table.*, city.name as city_name from $table, city $addFrom where city.id=$table.city_id $where $order";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top>";
    $body .= "<td align=center>".++$i.".</td>";
    if ($arrUser[1] == 'm' || $arrUser[1] == 'a') {
      $body .= "<td align=center nowrap>";
      if ($arrUser[1] == 'a' || ($arrUser[1] == 'm' && $row['manager_id'] == $arrUser[0])) {
        $body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
      } else {
        $body .= "&nbsp;";
      }
      $body .= "</td>";
    }
    

#    $body .= "<a href='$PHP_SELF?page=upd&num_str=".$row['id']."'><img src='/images/edit.gif' border=0></a>";
    if ($arrUser[1] == 'a' || ($arrUser[1] == 'm' && $row['manager_id'] == $arrUser[0])) {
      $body .= "<td><a href='$PHP_SELF?page=upd&num_str=".$row['id']."'>".stripslashes($row[name])."</a>&nbsp;</td>";
    } else {
      $body .= "<td><a href='$PHP_SELF?page=info&num_str=".$row['id']."'>".stripslashes($row[name])."</a>&nbsp;</td>";
    }
    $body .= "<td>".$row[city_name]."&nbsp;</td><td>".$row[contakt_fio]."&nbsp;</td><td>".$row[contakt]."&nbsp;</td></tr>\n"; 
	}
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
    $cgi["search_project_id"] = "";
    $cgi["search_status_id"] = "";
    $cgi["search_manager_id"] = "";
    $cgi["search_invite"] = "";
    $cgi["search_work_type"] = "";
    $cgi["search_name"] = "";
  } elseif (!isset($cgi["search_manager_id"]) && $arrUser[1] == 'm') {
    $cgi["search_manager_id"] = $arrUser[0];
  }

  $cont .= "<tr><td><b>Проект</b>&nbsp;&nbsp;</td><td>".do_combobox('search_project_id',$arrProject,$cgi["search_project_id"],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Статус работы</b>&nbsp;&nbsp;</td><td>".do_combobox('search_status_id',$lists[arrCompanyProjectStatus],$cgi["search_status_id"],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Менеджер</b>&nbsp;&nbsp;</td><td>".do_combobox('search_manager_id',$arrManager,$cgi["search_manager_id"],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Отправлено приглашение</b>&nbsp;&nbsp;</td><td>".do_combobox('search_invite',$lists['arrCheckYes'],$cgi["search_invite"],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Вид деятельности </b>&nbsp;&nbsp;</td><td>".do_combobox('search_work_type',$arrWorkType,$cgi["search_work_type"],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Название</b>&nbsp;&nbsp;</td><td><input name=search_name value='".$cgi["search_name"]."' size='50'></tr>\n";

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
		case 'MY2':

      $sql = "select id, $ref_2 from $ref_1 order by $ref_2";
      $sth = my_exec($sql);
      while($row = mysql_fetch_array($sth)) {
        $arr[$row['id']] = stripslashes($row[$ref_2]);
      }
      $arrWT = array();
      if (strlen($value) > 2) {
        $arrTemp = split(":",substr($value,1,-1));
        foreach($arrTemp as $kwt=>$vwt) {
          $arrWT[] = $arr[$vwt];
        }
      }
      if (count($arrWT)) {
        $cont .= join(", ",$arrWT);
      } else {
        $cont .= "Не выбрано ни одной позиции";
      }
			break;
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}
?>