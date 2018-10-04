<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

if(isset($cgi['return_dogovor'])) {
  header("Location:http://$HTTP_HOST/dogovor.php?page=upd&num_str=".$cgi['did']);
	exit();
}

#print_arr($cgi);

$text_menu = $objAdmMenu->GetMenu($user_id, 'Договора с экспонентами');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "service";

if(isset($cgi['add_row'])) {
	$sql = "insert into $table set dogovor_id = '".$cgi[did]."', service_id = '".$cgi[service_id]."', col = '".$cgi[col]."'";
	my_exec($sql);

  $text_new = "Договор = ".$cgi[did]."<br>ID доп. услуги = ".$cgi[service_id]."<br>Количество = ".$cgi[col];
  $query = "insert into statistic set user_id=$user_id, date=now(), type='insert', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Добавлена доп. услуга'";
  my_exec($query);
}

if(isset($cgi['upd_row'])) {
  $sql_old = "select * from $table where id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  $text_old .= "Договор = ".$row_old[dogovor_id]."<br>";
  $text_old .= "ID доп. услуги = ".$row_old[service_id]."<br>";
  $text_old .= "Количество = ".$row_old['col']."<br>";

	$sql = "update $table set service_id = '".$cgi[service_id]."', col = '".$cgi[col]."' where id=".$cgi['num_str'];
	my_exec($sql);

  $text_new = "ID доп. услуги = ".$cgi[service_id]."<br>Количество = ".$cgi['col'];
  $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='".$text_old."', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Обновлена доп. услуга'";
  my_exec($query);
}

if(isset($cgi['del_row'])) {
  $sql_old = "select * from $table where id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  $text_old .= "Договор = ".$row_old[dogovor_id]."<br>";
  $text_old .= "ID доп. услуги = ".$row_old[service_id]."<br>";
  $text_old .= "Количество = ".$row_old['col']."<br>";

	$sql = "delete from $table where id=".$cgi['num_str'];
#  echo $sql;
	my_exec($sql);

  $query = "insert into statistic set user_id=$user_id, date=now(), type='delete', text_old='".$text_old."', text_sql='".addslashes($sql)."', comment='Удалена доп. услуга'";
  my_exec($query);

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

include_once("usr_templ.php");


#######################################################################


function add($num_str) {
  global $filds, $body, $table, $lists, $cgi;

  $header = "Добавление доп. услуги и оборудования";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id='".$num_str."'";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
    $header = "Редактирование доп. услуги и оборудования";
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }

	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post enctype='multipart/form-data'>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";

	$sql = "select price.* from price, dogovor where dogovor.exhibition_id=price.exhibition_id and dogovor.id='".$cgi['did']."' order by price.name";
#			echo $sql;
	$sth_s = my_exec($sql);
  $arr = array();
	while($row_s = mysql_fetch_array($sth_s)) {
    $arr[$row_s[id]] = $row_s[name];
  }

	$body .= "<tr valign=top><td><b>Услуга / оборудование</b></td><td>".do_combobox('service_id',$arr,$row[service_id],'',1)."</td></tr>\n";
	$body .= "<tr valign=top><td><b>Количество</b></td><td><input name='col' size=10 value='".$row[col]."'></td></tr>\n";
	$body .= "</table><BR>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
}


function show() {
  global $body, $table, $cgi, $arrUser, $lists;
  $body = "<center><h4>Доп. услуги и оборудование</h4></center>";
  $body .= "
  <script language=JavaScript>
  function check_form(){
    if (document.form_1.service_id.value == '') {alert('Вы не указали, чего хотите добавить...'); form_1.service_id.focus(); return false;}
    if (document.form_1.col.value == '') {alert('Вы не указали количество...'); form_1.col.focus(); return false;}
    return true;
  }
  </script>
";
#  $body .= "<a href='$PHP_SELF?page=add&did=".$cgi[did]."'>Добавить запись</a><br>";
  $body .= "<br>";
  $body .= "<form method=post name=form_1><center>\n";
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
#  if ($arrUser[1] == 'm' || $arrUser[1] == 'a') {
	  $body .= "<td width=30>&nbsp;</td>";
#  }
	$body .= "<td><b>наименование</b></td>";
	$body .= "<td><b>цена</b></td>";
	$body .= "<td><b>количество</b></td>";
	$body .= "<td><b>единица изм.</b></td>";
	$body .= "<td><b>сумма</b></td>";
  $body .= "</tr>";

	$sql = "select price.* from price, dogovor where dogovor.exhibition_id=price.exhibition_id and dogovor.id='".$cgi['did']."' order by price.name";
#			echo $sql;
	$sth = my_exec($sql);
  $arr = array();
	while($row = mysql_fetch_array($sth)) {
    $arr[$row[id]] = $row[name];
  }

  $body .= "<tr valign=top>";
  $body .= "<td align=center><input type=submit name=add_row value='добавить' onClick='return check_form()'>\n";
	$body .= "<td>".do_combobox('service_id',$arr,'','',1)."</td>";
	$body .= "<td>&nbsp;</td>";
	$body .= "<td><input name=col size=5></td>";
	$body .= "<td>&nbsp;</td>";
	$body .= "<td>&nbsp;</td>";
  $body .= "</tr>";



	$sql = "select $table.id as s_id, $table.col, dogovor.currency_id, price.* from $table, price, dogovor where $table.service_id = price.id and $table.dogovor_id=dogovor.id and dogovor.id='".$cgi['did']."' order by s_id desc";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top>";
#    if ($arrUser[1] == 'm' || $arrUser[1] == 'a') {
      $body .= "<td align=center nowrap>";
      $body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['s_id']."&did=".$cgi[did]."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
      $body .= "</td>";
#    }

#    if ($arrUser[1] == 'a' || ($arrUser[1] == 'm' && $row['manager_id'] == $arrUser[0])) {
      $body .= "<td><a href='$PHP_SELF?page=upd&num_str=".$row['s_id']."&did=".$cgi[did]."'>".$row[name]."</a>&nbsp;</td>";
#    } else {
#      $body .= "<td><a href='$PHP_SELF?page=info&num_str=".$row['id']."'>".$row[name]."</a>&nbsp;</td>";
#    }

    if ($row['currency_id'] == 2) { # EURO
      $price = $row[price_eur]." &euro;";
      $summ = ($row[price_eur] * $row[col])." &euro;";
    } else {
      $price = $row[price_rub]." руб.";
      $summ = ($row[price_rub] * $row[col])." руб.";
    }
    $body .= "<td>".$price."&nbsp;</td><td>".$row[col]."&nbsp;</td><td>".$lists['arrEdIzm'][$row[ed_izm]]."&nbsp;</td><td>".$summ."&nbsp;</td></tr>\n"; 
	}
	$body .= "</table><br>\n";
#  $body .= "<form method=post><center><br>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
}



?>