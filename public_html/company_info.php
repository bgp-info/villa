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
  'inn'=>array('���','STR','','',40,10),
  'kpp'=>array('���','STR','','',40,9),
  'ras_schet'=>array('���. ����','STR','','',40,20),
  'kor_schet'=>array('���. ����','STR','','',40,20),
  'bank'=>array('����','STR','','',40),
  'bik'=>array('���','STR','','',40,9),

  'name'=>array('��������','STR'),
  'name_full'=>array('�������� ������','STR'),
  'name_engl'=>array('�������� ����.','STR'),
  'city_id'=>array('������','REF','city','name'),
  'address'=>array('����� ��. ���.','STR'),
  'address_engl'=>array('����� ��. ����.','STR'),
  'address_fakt'=>array('����� ����.','STR'),

  'phone'=>array('������','STR','','',40),
  'fax'=>array('����','STR','','',40),
  'email'=>array('E-mail','STR','','',40),
  'www'=>array('WWW','STR','','',40),

  'ruk_im'=>array('������������','STR'),
  'dolg_ruk_im'=>array('��������� ������������','STR'),

  'contakt_fio'=>array('���������� ����','STR'),
  'contakt'=>array('����������','STR'),
  'manager_id'=>array('�������','REF','manager','name'),

  'note'=>array('����������','TEXT'),

);


$sql = "select * from company where id='".(int)$cgi[id]."'";
#		echo $sql;
$sth = my_exec($sql);
$row = mysql_fetch_array($sth);

$body = "<p align=center><b>���������� �� ����������</b></p>";

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
$body .= "<input type=button onClick=window.close() value='  ������� ����  '>\n";
$body .= "</form></center>\n";

include_once("empty_templ.php");


?>