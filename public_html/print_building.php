<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

#print_arr($cgi);

$sql = "select id, name from company";
$sth = my_exec($sql);
$arrCompany = array();
while ($row = mysql_fetch_array($sth)) {
	$arrCompany[$row[id]] = $row[name];
}
$sql = "select id, name from manager where type='m' order by name";
$sth = my_exec($sql);
$arrManager = array();
while ($row = mysql_fetch_array($sth)) {
	$arrManager[$row[id]] = $row[name];
}

$text_menu = $objAdmMenu->GetMenu($user_id, 'Застройка');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

info($cgi['id']);      

include_once("empty_templ.php");


#######################################################################

  

function info($num_str) {
  global $filds, $body, $table, $arrUser, $arrCity, $arrManager, $lists;


    $sql = "select dogovor.*, stend.number as s_numver, stend.friz as s_friz, stend.ext, company.name as c_name, company.name_full as c_name_full, company.contakt, company.contakt_fio from dogovor left join stend on stend.dogovor_id = dogovor.id left join company on dogovor.client_id = company.id where dogovor.id = '".$num_str."'";
#    echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);

    $body .= "<center><p align=centre><b>Подробная информация по экспоненту ".stripslashes($row[c_name])."</b></p>";

    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";

    $body .= "<tr valign=top><td height=26><b>Название экспонента</b></td><td>".stripslashes($row[c_name_full])."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>Номер договора</b></td><td>".make_num($row[number])."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>Площадь в павильоне</b></td><td>".$row[area_all]."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>№ стенда</b></td><td>".$row[s_numver]."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>Открытая площадь</b></td><td>".$row[area_open]."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>Стандартное оборудование</b></td><td>".$lists['arrEquipment'][$row[equipment_id]]."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>Надпись на фризе</b></td><td>".stripslashes($row[s_friz])."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>Контактное лицо</b></td><td>".stripslashes($row[contakt_fio])."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26><b>Координаты</b></td><td>".stripslashes($row[contakt])."&nbsp;</td></tr>\n";
    $body .= "<tr valign=top><td height=26 colspan=2><b>Дополнительные услуги и оборудование </b></td></tr>\n";
    $body .= "<tr valign=top><td colspan=2>";
    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr valign=top bgcolor=dddddd><td height=26><b>Название</b></td><td><b>Количество</b></td></tr>\n";
    
    $sql = "select price.name, service.col from service, price where price.id = service.service_id and dogovor_id = '".$row[id]."'";
    $sth_s = my_exec($sql);
    while ($row_s = mysql_fetch_array($sth_s)) {
      $body .= "<tr valign=top><td height=26><b>".$row_s[name]."</b></td><td>".$row_s[col]."&nbsp;</td></tr>\n";
    }
    $body .= "</table>\n";
    $body .= "</td></tr>\n";
    if ($row[ext] && $row[ext] != '---') {
      $body .= "<tr valign=top><td align=center colspan=2><img src='/picture_stend/pic_".$row[id].$row[ext]."'></td></tr>\n";
    }
    $body .= "</table><BR>\n";
    $body .= "<p>Утверждаю _______________________ </p>\n";


    $body .= "<form method=post>\n";
    $body .= "<INPUT onclick=window.print() type=button value=' Печатать ' class=show_btn>&nbsp;&nbsp;&nbsp;";
    $body .= "<input type=button onClick=window.close() value='  Закрыть окно  ' class=show_btn>\n";
    $body .= "</form></center>\n";
  return $body;
}



?>