<?php

#include_once("adm_menu.inc");
include_once("../../private/conf.inc");
include_once("../../private/func.inc");
if (!authenticateAdmin ($cookie_adm, $cookie_adm_passwd)) {
	header("Location:http://$HTTP_HOST/admin/index.php");
	exit();
}
#$text_menu = $objAdmMenu->GetMenu('Выставки');
$text_menu = "";
$table = "price_main";

$exhib = $cgi[exhib];
$sql = "select name from exhibition where id=".$exhib;
$sth = my_exec($sql);
$row = mysql_fetch_array($sth);
$exhib_name = $row['name'];



if(isset($cgi['upd_row'])) {
  foreach($cgi as $k=>$v) {
    if (substr($k,0,8) == 'serv_rub') {
      $serv_id = substr($k,9);
      $price_rub = ereg_replace(",",".",trim($v));
      $price_eur = ereg_replace(",",".",trim($cgi['serv_eur_'.$serv_id]));
      $sql = "replace $table set exhibition_id='".$cgi[exhib]."', service_id='".$serv_id."', price_rub='".$price_rub."', price_eur='".$price_eur."'";
      my_exec($sql);
#      echo "$sql<BR>";
    }
  }
#  $sql = "replace $table set ".implode(",",$arr_3)." where id=".$cgi['num_str'];
#  my_exec($sql);
}

add($cgi[exhib]);      

include_once("adm_templ.php");


#######################################################################


function add($num_str) {
  global $body, $table, $exhib, $exhib_name, $lists;

  $sql = "select * from $table where exhibition_id=$num_str";
  #		echo $sql;
  $sth = my_exec($sql);
  $arrVal = array();
  while($row = mysql_fetch_array($sth))  {
    $arrVal[$row[service_id]] = $row;
  }
  /*
  reset($filds);
  while (list($k,$v) = each($filds)) {
    $$k=$row[$k];
  }
  */
  $name_btn = "upd_row";
  $text_btn = "Обновить";

	$body = "<center><h4>Редактирование основных услуг проекта $exhib_name</h4>";
  $body .= "<form method=post>\n";
	$body .= "<table class=small width=95% border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  foreach($lists['arrMainService'] as $k=>$v) {
	  $body .= "<tr valign=top><td><b>".$v."</b></td>";
    $body .= "<td>".do_input('serv_rub_'.$k,$arrVal[$k][price_rub],'NUM','','',10,'')." руб.</td>";
    $body .= "<td>".do_input('serv_eur_'.$k,$arrVal[$k][price_eur],'NUM','','',10,'')." &euro;</td>";
    $body .= "</tr>\n";
  }
	$body .= "</table><BR>\n";
	$body .= "<input type=hidden name=exhib value='$exhib'>\n";
	$body .= "<input type=submit name=$name_btn value='  $text_btn  '>&nbsp;&nbsp;&nbsp;\n";
	$body .= "<input type=button value='Закрыть окно' onClick='window.close()'>";
  $body .= "</form></center>\n";
  return $body;
}



?>