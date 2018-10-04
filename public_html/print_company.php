<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

#print_arr($cgi);

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


$text_menu = $objAdmMenu->GetMenu($user_id, 'Экспоненты');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "company";

$filds = array(
  'inn'=>array('ИНН','STR','','',40,10),
  'kpp'=>array('КПП','STR','','',40,9),
  'ras_schet'=>array('Рас. счет','STR','','',40,20),
  'kor_schet'=>array('Кор. счет','STR','','',40,20),
  'bank'=>array('Банк','STR','','',40),
  'bik'=>array('БИК','STR','','',40,9),

  'name'=>array('Название','STR'),
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
  'manager_id'=>array('Куратор','REF','manager','name'),
);
reset($filds);

info($cgi[id]);      

include_once("empty_templ.php");


#######################################################################



function info($num_str) {
  global $filds, $body, $table, $arrUser, $arrCity, $arrManager;

  $sql = "select * from $table where id=$num_str";
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  $header = "Информация по экспоненту";
  reset($filds);
  while (list($k,$v) = each($filds)) {
    $$k=$row[$k];
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
    if ($$k) {
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
	}
	$body .= "</table><BR>\n";
  $body .= "<INPUT onclick=window.print() type=button value=' Печатать ' class=show_btn>&nbsp;&nbsp;&nbsp;";
  $body .= "<input type=button onClick=window.close() value='  Закрыть окно  ' class=show_btn>\n";
  $body .= "</form></center>\n";
  return $body;
}



?>