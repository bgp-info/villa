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

$table = "plateg";


$cgi[search_date_1_def]='2000-01-01';
$cgi[search_date_2_def]='2010-01-01';
if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

$sql = "select * from dogovor where id='".$cgi['did']."'";
$sth = my_exec($sql); 
$dogInfo = mysql_fetch_array($sth);


if(isset($cgi['add_row'])) {
	$sql = "insert into $table set dogovor_id = '".$cgi[did]."', name = '".addslashes($cgi[name])."', date = '".$cgi[date]."', summ = '".$summ."', currency_id = '".$cgi[currency_id]."'";
	my_exec($sql);

  $text_new = "Договор = ".$cgi[did]."<br>Номер п/п = ".addslashes($cgi[name])."<br>Дата платежа = ".$cgi[date]."<br> summ = ".$cgi['summ'];
  $query = "insert into statistic set user_id=$user_id, date=now(), type='insert', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Добавлен платеж'";
  my_exec($query);
}

if(isset($cgi['upd_row'])) {
  $sql_old = "select * from $table where id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  $text_old .= "Договор = ".$row_old[dogovor_id]."<br>";
  $text_old .= "Номер п/п = ".addslashes($row_old[name])."<br>";
  $text_old .= "Дата платежа = ".$row_old[date]."<br>";
  $text_old .= "summ = ".$row_old['summ']."<br>";

	$sql = "update $table set name = '".addslashes($cgi[name])."', date = '".$cgi[date]."', summ = '".$summ."', currency_id = '".$cgi[currency_id]."' where id=".$cgi['num_str'];
	my_exec($sql);

  $text_new = "Номер п/п = ".addslashes($cgi[name])."<br>Дата платежа = ".$cgi[date]."<br> summ = ".$cgi['summ'];
  $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='".$text_old."', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Обновлен платеж'";
  my_exec($query);
}

if(isset($cgi['del_row'])) {
  $sql_old = "select * from $table where id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  $text_old .= "Договор = ".$row_old[dogovor_id]."<br>";
  $text_old .= "Номер п/п = ".addslashes($row_old[name])."<br>";
  $text_old .= "Дата платежа = ".$row_old[date]."<br>";
  $text_old .= "summ = ".$row_old['summ']."<br>";

  $sql = "delete from $table where id = ".$cgi['num_str'];
  my_exec($sql);

  $query = "insert into statistic set user_id=$user_id, date=now(), type='delete', text_old='".$text_old."', text_sql='".addslashes($sql)."', comment='Удален платеж'";
  my_exec($query);
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
  global $filds, $body, $table, $lists, $cgi, $dogInfo;


  $header = "Добавление платежного поручения";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  $row[currency_id] = $dogInfo[currency_id];
  if ($num_str != 0) {
    $sql = "select * from $table where id='".$num_str."'";
    #		echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
    $header = "Редактирование платежного поручения";
    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }

	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top height=33><td><b>Номер п/п</b></td><td><input  name='name' value='".stripslashes($row[name])."' size=70></td></tr>\n";
	$body .= "<tr valign=top><td><b>Дата платежа</b></td><td>".do_input('date',$row[date],'DATE','','','','')."</td></tr>\n";
	$body .= "<tr valign=top><td><b>Сумма</b></td><td><input name='summ' value='".$row[summ]."' size=10> ".$lists['arrCurrency'][$row[currency_id]]."<input type=hidden name=currency_id value='".$row[currency_id]."'></td></tr>\n";
#	$body .= "<tr valign=top><td><b>Валюта</b></td><td>".do_combobox('currency_id',$lists['arrCurrency'],$row[currency_id],'','')."</td></tr>\n";

	$body .= "</table><BR>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
}

function info($num_str) {
  global $filds, $body, $table, $lists, $cgi, $dogInfo;

  $sql = "select * from $table where id='".$num_str."'";
  #		echo $sql;
  $sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  $header = "Просмотр платежного поручения";

	$body = "<center><h4>$header</h4>";
  $body .= "<form method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top height=33><td><b>Номер п/п</b></td><td>".stripslashes($row[name])."</td></tr>\n";
	$body .= "<tr valign=top><td><b>Дата платежа</b></td><td>".mysql2date2($row[date])."</td></tr>\n";
	$body .= "<tr valign=top><td><b>Сумма</b></td><td>".$row[summ]." ".$lists['arrCurrency'][$row[currency_id]]."</td></tr>\n";
#	$body .= "<tr valign=top><td><b>Валюта</b></td><td>".$lists['arrCurrency'][$row[currency_id]]."</td></tr>\n";

	$body .= "</table><BR>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
}

function show() {
  global $body, $table, $cgi, $arrUser, $lists;
  $body = "<center><h4>Платежи</h4></center>";
#  print_arr($arrUser);
#	$arr_search = array(
#    'manager_id'=>array('Менеджер','REF','manager','name',1),
#		'name'=>array('Название','STR'),
#		);
#	$body = do_search_here($arr_search);
  if ($arrUser[1] == 'b') {
    $body .= "<a href='$PHP_SELF?page=add&did=".$cgi[did]."'>Добавить запись</a><br><br>";
  } else {
    $body .= "<br><br>";
  }
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  if ($arrUser[1] == 'b') {
	  $body .= "<td width=30>&nbsp;</td>";
  }
	$body .= "<td><b>номер п/п</b></td>";
	$body .= "<td><b>дата</b></td>";
	$body .= "<td><b>сумма</b></td>";
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
	if($cgi[oldOrdCol]){
		if ($cgi[oldOrdCol] == 'name') {
			$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
		}	elseif ($cgi[oldOrdCol] == 'city_id') {
			$order = " order by city_name ".$cgi[ordDesc];
		}
  } else {
    $order = " order by $table.id desc";
  }
  if (count($arrWhere)) {
		$where = "and ".join(" and ",$arrWhere);
	}
  if ($cgi["btn_all"]) {
		$where = "";
	}
	$sql = "select * from $table where $table.dogovor_id='".$cgi['did']."' $where $order";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {

		$body .= "<tr valign=top>";
    if ($arrUser[1] == 'b') {
      $body .= "<td align=center nowrap>";
      $body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."&did=".$cgi[did]."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
      $body .= "</td>";
    }

    if ($arrUser[1] == 'b') { 
      $body .= "<td><a href='$PHP_SELF?page=upd&num_str=".$row['id']."&did=".$cgi[did]."'>".$row[name]."</a>&nbsp;</td>";
    } else {
      $body .= "<td><a href='$PHP_SELF?page=info&num_str=".$row['id']."&did=".$cgi[did]."''>".$row[name]."</a>&nbsp;</td>";
    }
    $body .= "<td>".mysql2date2($row[date])."&nbsp;</td>";
    $body .= "<td>".$row[summ]." ".$lists['arrCurrency'][$row[currency_id]]."&nbsp;</td>";
	}
	$body .= "</table>\n";
  $body .= "<form method=post><center><br>\n";
  $body .= "<input type=hidden name=did value=".$cgi[did].">\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  return $body;
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
      if ($name='service_id') {
			  $cont = do_combobox($name,$arr,$value,' style=\'width: 500\'','');
      } else {
			  $cont = do_combobox($name,$arr,$value,'','');
      }
			break;
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}
?>