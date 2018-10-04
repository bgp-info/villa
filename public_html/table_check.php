<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}
#print_arr($cgi);


$text_menu = $objAdmMenu->GetMenu($user_id, 'Контроль застройки');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);
#echo $this_project;

$table = "dogovor";


$fields = array(
  'number' => array('name'=>'Договор', 'sort'=>'Y'),
  'stend' => array('name'=>'Стенд', 'sort'=>'Y'),
  'c_name' => array('name'=>'Экспонент', 'sort'=>'Y'),
  'area_all' => array('name'=>'Площадь в павильоне', 'sort'=>'Y'),
  'equipment_id' => array('name'=>'Тип застройки', 'sort'=>'Y'),
  'area_open' => array('name'=>'Открытая площадь', 'sort'=>'Y'),
  'note' => array('name'=>'Примечания', 'sort'=>'N'),
  'service' => array('name'=>'Доп. оборудование<br><img src="/images/1x1.gif" width=400 height=1>', 'sort'=>'N'),
  'friz' => array('name'=>'Фриз', 'sort'=>'N'),
  'manager' => array('name'=>'Ответственный', 'sort'=>'Y'),
  'is_ok' => array('name'=>'Стенд утвержден', 'sort'=>'Y'),
  'ext' => array('name'=>'Схема стенда', 'sort'=>'Y'),
);


$sql = "select * from price_main";
$sth = my_exec($sql);
$arrInfo = array();
while ($row = mysql_fetch_array($sth)) {
	$arrInfo[$row[exhibition_id]][$row[service_id]][rub] = $row[price_rub];
	$arrInfo[$row[exhibition_id]][$row[service_id]][eur] = $row[price_eur];
}

$sql = "select id, name from manager where type='m' order by name";
$sth = my_exec($sql);
$arrManager = array();
while ($row = mysql_fetch_array($sth)) {
	$arrManager[$row[id]] = $row[name];
}

$sql = "select * from stend";
$sth = my_exec($sql);
$arrStend = array();
while ($row = mysql_fetch_array($sth)) {
	$arrStend[$row[dogovor_id]][number] = stripslashes($row[number]);
	$arrStend[$row[dogovor_id]][friz] = stripslashes($row[friz]);
	$arrStend[$row[dogovor_id]][note] = stripslashes($row[note]);
}

$cgi[search_date_1_def]='2000-01-01';
$cgi[search_date_2_def]='2010-01-01';
if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

$cgi['exhibition_id'] = $this_project;

show();

include_once("usr_templ.php");


#######################################################################


function show() {
  global $body, $table, $fields, $cgi, $user_id, $arrUser, $lists, $arrInfo, $arrStend, $cmp_desc, $this_project, $arrManager;

  $arrEE = array(1=>'самострой', 2=>'стандарт', 3=>'октонорм');

  $sql = "select service.dogovor_id, service.col, price.name, price.ed_izm from service, price where service.service_id = price.id";
  $sth = my_exec($sql);
  $arrServ = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrServ[$row[dogovor_id]][] = "<img src='/images/galka.gif'> ".$row[name]." - ".$row[col]." ".$lists['arrEdIzm'][$row[ed_izm]];
  }

#  print_arr($arrServ);

  $body = do_search_here();
  $body .= "<br>";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1><td width=30>№</td>";


  foreach ($fields as $k=>$v) {
    if ($v['sort'] == 'Y') {
      if($cgi[oldOrdCol] == $k){
        if($cgi[ordDesc] == 'desc'){
          $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
        }else{
          $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
        }
      } else {
        $str = "";
      }
      $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='".$k."'; document.form_search.submit();return false;\"><b><font color=white>".$v['name']."</font></b></a>&nbsp;&nbsp;$str</td>";
    } else {
      $body .= "<td><b>".$v['name']."</b></td>";
    }
  }
  $body .= "</tr>";

  if ($cgi['search_manager_id']) {
		$where .= " and c.manager_id=".$cgi['search_manager_id'];
	}
  if ($cgi['search_equipment_id'] == 'std') {
		$where .= " and $table.equipment_id in (2,3)";
	} elseif ($cgi['search_equipment_id'] == 'sam') {
		$where .= " and $table.equipment_id = 1";
	}
  if ($cgi['search_type_area'] == 'pav') {
		$where .= " and $table.area_all > 0";
	} elseif ($cgi['search_type_area'] == 'otk') {
		$where .= " and $table.area_open > 0";
	}
  if ($cgi["btn_all"]) {
		$where = "";
	}

  $where_main = " and $table.exhibition_id=".$this_project;
  #$where_main .= " and $table.equipment_id in (2,3)";

	$sql = "select $table.*, c.name as c_name, c.manager_id as c_manager, stend.is_ok, stend.ext from company c, $table left join stend on stend.dogovor_id = $table.id where c.id=$table.client_id $where_main $where";
	#		echo $sql;
	$sth = my_exec($sql);
  $arrAll = array();
	while($row = mysql_fetch_array($sth)) {
    $row[stend] = $arrStend[$row['id']][number];
    $row[friz] = $arrStend[$row['id']][friz];
    $row[note] = $arrStend[$row['id']][note];
    $row[manager] = $arrManager[$row['c_manager']];
    $row[is_ok] = $row['is_ok']=='Y'?"<img src='/images/galka.gif'>":"";
    $row[ext] = $row['ext']!='---'?"<img src='/images/galka.gif'>":"";
    if (count($arrServ[$row['id']])) {
      $row[service] = join(",<br>",$arrServ[$row['id']]);
    }
    $arrAll[$row[id]] = $row;
  }
  #print_arr($arrAll);


  #print_arr($cgi);

  if($cgi[oldOrdCol]){
    if ($cgi[oldOrdCol] == 'c_name') {
      masort($arrAll, $cgi[oldOrdCol].(trim($cgi[ordDesc])=='desc'?" ".trim($cgi[ordDesc]):""));
    } else {
      masort($arrAll, $cgi[oldOrdCol].(trim($cgi[ordDesc])=='desc'?" ".trim($cgi[ordDesc]):"").", c_name");
    }
  } else {
    masort($arrAll, "number");
  }

  #print_arr($arrAll);

  $i = 0;
  foreach ($arrAll as $k=>$row) { # Сперва все действующие
    if ($row[status_id] == 0) {
      $s1 = $s2 = $bg = "";
      $all_area_all += $row[area_all];
      $all_area_open += $row[area_open];
      $body .= "<tr valign=top align=center $bg><td nowrap>".(++$i)."</td>";
      $body .= "<td>$s1<a href='/dogovor.php?page=info&num_str=".$row['id']."' target='_blank'>".make_num($row[number])."</a>$s2</td>";
      $body .= "<td>$s1".$row[stend]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1<a href=# onclick=\"return w_o('/company_info.php?id=".$row[client_id]."', 700,700, ',resizable=1,scrollbars=1')\">".stripslashes($row[c_name])."</a>$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[area_all]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$arrEE[$row[equipment_id]]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[area_open]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[note]."$s2&nbsp;</td>"; 
      $body .= "<td align=left>$s1".$row[service]."$s2&nbsp;</td>"; 
      $body .= "<td align=left>$s1".$row[friz]."$s2&nbsp;</td>"; 
      $body .= "<td align=left>$s1".$row[manager]."$s2&nbsp;</td>"; 
      $body .= "<td>$s1".$row[is_ok]."$s2&nbsp;</td>"; 
      $body .= "<td>$s1".$row[ext]."$s2&nbsp;</td>"; 
      $body .= "</tr>\n";
    }
	}

  foreach ($arrAll as $k=>$row) { # Затем не действующие
    if ($row[status_id] == 1) {
      $s1 = "<s>";
      $s2 = "</s>";
      $bg = "bgcolor=dddddd";
      $body .= "<tr valign=top align=center $bg><td nowrap>".(++$i)."</td>";
      $body .= "<td>$s1<a href='/dogovor.php?page=info&num_str=".$row['id']."' target='_blank'>".make_num($row[number])."</a>$s2</td>";
      $body .= "<td>$s1".$row[stend]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1<a href=# onclick=\"return w_o('/company_info.php?id=".$row[client_id]."', 700,700, ',resizable=1,scrollbars=1')\">".$row[c_name]."</a>$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[area_all]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$arrEE[$row[equipment_id]]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[area_open]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[note]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[service]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[friz]."$s2&nbsp;</td>";
      $body .= "<td align=left>$s1".$row[manager]."$s2&nbsp;</td>"; 
      $body .= "<td>$s1".$row[is_ok]."$s2&nbsp;</td>"; 
      $body .= "<td>$s1".$row[ext]."$s2&nbsp;</td>"; 
      $body .= "</tr>\n";
    }
  }

  $body .= "<tr valign=top align=right><td colspan=4><b>Итого:&nbsp;&nbsp;</b></td>";
	$body .= "<td><b>".$all_area_all."</b>&nbsp;</td>";
	$body .= "<td>&nbsp;</td>";
	$body .= "<td><b>".$all_area_open."</b>&nbsp;</td>";
	$body .= "<td colspan=6><b>&nbsp;</td>";
  $body .= "</tr>";
	$body .= "</table>\n";
  return $body;
}

function do_search_here() {
	global $cgi, $table, $arrManager;
  $arrE = array('std'=>'стандарт и октонорм', 'sam'=>'самострой');
  $arrTP = array('pav'=>'павильон', 'otk'=>'открытая');
  if($cgi[ordCol]){
    if($cgi[ordCol] != $cgi[oldOrdCol]){
      $cgi[ordDesc] = 'asc';
    }else{
      if($cgi[ordDesc] == 'desc'){
        $cgi[ordDesc] = 'asc';
      }else{
        $cgi[ordDesc] = 'desc';
      }
    }
    $cgi[oldOrdCol] = $cgi[ordCol];
    $cgi[ordCol] = '';
  }
	$cont = "<form name=form_search method=post>\n<table border=1 cellspacing=0 cellpadding=10 bordercolorlight=black bordercolordark=white align=center>\n";
	$cont .= "<tr><td align=center>";
	$cont .= "<table border=0>";

  if ($cgi['btn_all']) {
    $cgi[search_manager_id] = '';
    $cgi[search_equipment_id] = '';
    $cgi[search_type_area] = '';
  }
  $cont .= "<tr><td><b>Менеджер</b>&nbsp;&nbsp;</td><td>".do_combobox("search_manager_id",$arrManager,$cgi[search_manager_id],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Тип застройки</b>&nbsp;&nbsp;</td><td>".do_combobox("search_equipment_id",$arrE,$cgi[search_equipment_id],'',1)."</td></tr>\n";
  $cont .= "<tr><td><b>Тип площади</b>&nbsp;&nbsp;</td><td>".do_combobox("search_type_area",$arrTP,$cgi[search_type_area],'',1)."</td></tr>\n";

	$cont .= "</table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
	$cont .= "<br><input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
	$cont .= "<input type=submit name=btn_search value='Поиск'>";
	$cont .= "</td></tr></table></form>\n";

	return $cont;
}


?>