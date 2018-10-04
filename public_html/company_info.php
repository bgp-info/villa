<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

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
  'manager_id'=>array('Куратор','REF','manager','name'),

  'note'=>array('Примечание','TEXT'),

);


$sql = "select * from company where id='".(int)$cgi[id]."'";
#		echo $sql;
$sth = my_exec($sql);
$row = mysql_fetch_array($sth);

$body = "<p align=center><b>Информация по экспоненту</b></p>";

$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
reset($filds);
while (list($k,$v) = each($filds)) {
  if ($k == 'city_id') {
    $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$arrCity[$row[$k]]."&nbsp;</td></tr>\n";
  } elseif ($k == 'manager_id') {
    $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$arrManager[$row[$k]]."&nbsp;</td></tr>\n";
  } else {
    $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".stripslashes($row[$k])."&nbsp;</td></tr>\n";
  }
}
$body .= "</table><br>";

$body .= "<center><form>";
$body .= "<input type=button onClick=window.close() value='  Закрыть окно  '>\n";
$body .= "</form></center>\n";

include_once("empty_templ.php");


?>