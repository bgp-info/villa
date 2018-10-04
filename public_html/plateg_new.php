<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

$text_menu = $objAdmMenu->GetMenu($user_id, 'Платежи');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "plateg";

if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

if(isset($cgi['add_row'])) {
	if ($cgi['name']) {
		$sql = "insert into $table set dogovor_id = '".$cgi[dogovor_id]."', name = '".addslashes($cgi[name])."', date = '".$cgi[date]."', summ = '".$cgi[itogo_summ]."', currency_id = '".$cgi[currency_id]."'";
    #echo $sql;
		my_exec($sql);
    $plateg_id = mysql_insert_id();
    
    foreach ($cgi as $k=>$v) {
      if (ereg("pl_([0-9]+)",$k,$r)) {
        $id = $r[1];
		    $sql = "insert into plateg_schet set plateg_id = '".$plateg_id."', schet_id = '".$cgi["snum_".$id]."', summ = '".$v."', is_all = '".($cgi["s_".$id]==$cgi["pl_".$id]?'Y':'N')."'";
        my_exec($sql);
      }
    }
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_row'])) {
	if ($cgi['summ']) {
		$sql = "update $table set summ = '".$cgi[summ]."' where id=".$cgi['num_str'];
    #echo $sql;
		my_exec($sql);
    $sql = "update plateg_schet set summ = '".$cgi[summ]."', is_all = 'N' where plateg_id = '".$cgi['num_str']."'";
    my_exec($sql);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

#print_arr($cgi);

if(isset($cgi['del_row'])) {
	//$is_link=0;
	//$query = "select * from dogovor where client_id = ".$cgi['num_str'];
	//$result = my_exec($query);
	//if (mysql_num_rows($result))  { $is_link=1; $whereIt .= " В разделе Договора с экспонентами, "; }

	//if ($is_link) { 
  //  $error=red2("Запись не может быть удалена, т.к. существуют связанные с ней записи. <br>".substr($whereIt,0,-2)."<br>Сперва удалите их!");
  //} else {
		$sql = "delete from $table where id = ".$cgi['num_str'];
		my_exec($sql);
	//}
}

$sql = "select company.name as c_name, dogovor.* from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' order by company.name, dogovor.number";
$sth = my_exec($sql);
$arrClient = array();
while ($row = mysql_fetch_array($sth)) {
  $arrClient[$row[id]] = stripslashes($row[c_name])." (договор № ".$row[number].")";
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
  global $body, $cgi, $table, $arrUser, $arrManager, $lists, $this_project, $arrClient;

  //if ($arrUser[1] == 'm') {
	//  $manager_id = $arrUser[0];
  //}

  $header = "Добавление платежного поручения";
  $name_btn = "add_row";
  $text_btn = "Добавить";
  if ($num_str != 0) {
    $sql = "select * from $table where id=$num_str";
    #		echo $sql;
    $sth = my_exec($sql);
    $row_main = mysql_fetch_array($sth);
		$header = "Редактирование платежного поручения";

    $name_btn = "upd_row";
    $text_btn = "Обновить";
  }

  $dogovor_id = $cgi[dogovor_id]?$cgi[dogovor_id]:$row_main[dogovor_id];
  $date = $cgi[date]?$cgi[date]:$row_main[date];
  $name = $cgi[name]?stripslashes($cgi[name]):stripslashes($row_main[name]);
  $text = $cgi[text]?stripslashes($cgi[text]):stripslashes($row_main[text]);
  $summ = $row_main[summ];
  $currency_id = $row_main[currency_id];

	$body = "<center><h4>$header</h4>";

  $body .= "
  <script language=javascript>
  function make_close(i) {
    var ss = eval('document.f1.s_'+i+'.value');
    var sp = eval('document.f1.p_'+i+'.value');
    var pl = ss - sp;
    eval('document.f1.pl_'+i+'.value = pl');
    var si = document.f1.itogo_summ.value;
    var all = si*1 + pl*1;
    document.f1.itogo_summ.value = all;
    if (document.getElementById) {
      var fe01 = document.getElementById('isumm');
    } else if (document.all) {
      var fe01 = document.all['isumm'];
    }
    fe01.innerHTML = all;
    //alert(ss);
  }
  function make_recount() {
    var all = 0;
    for (i=0; i<document.f1.elements.length; i++) {
      if ((document.f1.elements[i].name.substring(0,3)) == 'pl_') {
        all = all + document.f1.elements[i].value * 1;
      }
    }
    document.f1.itogo_summ.value = all;
    if (document.getElementById) {
      var fe01 = document.getElementById('isumm');
    } else if (document.all) {
      var fe01 = document.all['isumm'];
    }
    fe01.innerHTML = all;
    //alert(ss);
  }
  </script>
  ";

  $body .= "<form name=f1 method=post>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<input type=hidden name=page>\n";


	

  if (!$num_str) { # Новый...
    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr valign=top><td><b>Номер п/п</b></td><td><input name=name value='".$name."' size='10'></td></tr>\n";
    $body .= "<tr valign=top><td><b>Дата</b></td><td>".do_input('date',$date,'DATE','','','','')."</td></tr>\n";
    $body .= "<tr valign=top><td><b>Плательщик</b></td><td>".do_combobox('dogovor_id',$arrClient,$dogovor_id,' style="width:450px" onChange="document.f1.page.value=\'upd\'; document.f1.submit();"',1)."</td></tr>\n";
    if ($dogovor_id) {
      $body .= "<tr valign=top><td><b>Сумма</b></td><td>";
      $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
      $body .= "<tr valign=top bgcolor=e0e0e0><td><b>Номер счета</b></td><td><b>Сумма счета</b></td><td><b>Закрыто</b></td><td><b>Текущий платеж</b></td></tr>\n";
  /*

      */
      $sql_s = "select * from schet where dogovor_id = $dogovor_id";
      $sth_s = my_exec($sql_s);
      $arrS = array();
      while ($row_s = mysql_fetch_array($sth_s)) {
        $arrS[$row_s[id]] = $row_s;
      }
      if (count($arrS)) {
        $sql_sp = "select * from plateg_schet where schet_id in (".join(",",array_keys($arrS)).")";
        $sth_sp = my_exec($sql_sp);
        $arrSP = array();
        while ($row_sp = mysql_fetch_array($sth_sp)) {
          $arrSP[$row_sp[schet_id]] += $row_sp[summ];
        }
      }
      

      $i = 0;
      foreach($arrS as $k=>$row_s) {
        $body .= "<tr valign=top><td><input type=hidden name=snum_".$i." value='".$k."'>№ ".$row_s[number]." от ".mysql2date2($row_s[date])."</td><td>".$row_s[summ]."<input type=hidden name=s_".$i." value='".$row_s[summ]."'></td><td>".($arrSP[$row_s[id]]?$arrSP[$row_s[id]]:0)."<input type=hidden name=p_".$i." value='".($arrSP[$row_s[id]]?$arrSP[$row_s[id]]:0)."'></td><td><input name=pl_".$i." size=10>&nbsp;&nbsp;<a href=# onclick='make_close(".$i.");'>закрыть полностью</a></td></tr>\n";
        $i++;
      }
      $body .= "<tr valign=top><td colspan=3 align=right><b>Итого:&nbsp;&nbsp;</b></td><td><input type=hidden name=itogo_summ><b><span id=isumm>0</span></b></td></tr>\n";


      $body .= "</table><BR>\n";
      $body .= "</td></tr>\n";
    } else {
      $body .= "<tr valign=top><td><b>Сумма</b></td><td>сперва выберите плательщика...</td></tr>\n";
    }
    $body .= "<tr valign=top><td><b>Валюта</b></td><td>".do_combobox('currency_id',$lists['arrCurrency'],$row_main[currency_id],'','')."</td></tr>\n";
    $body .= "</table><BR>\n";
    $body .= "<input type=button name=recount value='  Пересчитать  ' onClick=make_recount();>&nbsp;&nbsp;&nbsp;<input type=submit name=$name_btn value='  $text_btn  '>\n";
  } else {
    $sql_sp = "select id from plateg_schet where plateg_id = ".$num_str;
    $sth_sp = my_exec($sql_sp);
    $row_sp = mysql_fetch_array($sth_sp);

    $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr valign=top><td><b>Номер п/п</b></td><td><b>".$name."</b></td></tr>\n";
    $body .= "<tr valign=top><td><b>Дата</b></td><td>".mysql2date2($date)."</td></tr>\n";
    $body .= "<tr valign=top><td><b>Плательщик</b></td><td>".$arrClient[$dogovor_id]."</td></tr>\n";
    if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
      $body .= "<tr valign=top><td><b>Сумма</b></td><td><input name=summ value='".$summ."' size='10'></td></tr>\n";
    } else {
      $body .= "<tr valign=top><td><b>Сумма</b></td><td><b>".$summ."</b></td></tr>\n";
    }
    $body .= "<tr valign=top><td><b>Валюта</b></td><td>".$lists['arrCurrency'][$currency_id]."</td></tr>\n";
    $body .= "<tr valign=top><td><b>Назначение</b></td><td>".$text."&nbsp;</td></tr>\n";
    $body .= "</table><BR>\n";
    if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
      $body .= "<input type=hidden name=schet_id value='".$row_sp[sp_id]."'>\n";
      $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
    } else {
      $body .= "<input type=button name=ret value='  Вернуться  ' onClick='history.back()'>\n";
    }
  }


  $body .= "</form></center>\n";

  return $body;
}


function show() {
  global $body, $table, $cgi, $arrUser, $this_project, $arrMoney, $lists;
#  print_arr($arrUser);


  # $body = "<h2 color=red align=center>Внимание!!! Инструмент не готов!!! Пользоваться нельзя!!!</h2><br><br>";
	$body .= do_search_here();
  #if ($arrUser[1] == 'm' || $arrUser[1] == 'a') {
  if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
    $body .= "<table width=100%><tr><td><a href='$PHP_SELF?page=add'>Добавить запись</a></td><td align=right><a href=# onClick=\"w_o('/cron/cron_get_xml.php',300,300);\">Загрузка из 1С</a></td></tr></table><br>";
  } else {
    $body .= "<br><br>";
  }
	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
  $body .= "<td width=30><b>№</b></td>";
  if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
	  $body .= "<td width=30>&nbsp;</td>";
  }

  if($cgi[oldOrdCol] == 'name'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='name'; document.form_search.submit();return false;\"><b><font color=white>Номер п/п</font></b></a>&nbsp;&nbsp;$str</td>";

  if($cgi[oldOrdCol] == 'date'){
    if($cgi[ordDesc] == ' desc '){
      $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
    }else{
      $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
    }
  } else {
    $str = "";
  }
  $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='date'; document.form_search.submit();return false;\"><b><font color=white>Дата</font></b></a>&nbsp;&nbsp;$str</td>";

	$body .= "<td><b>Плательщик</b></td>";
	$body .= "<td><b>Сумма, руб.</b></td>";
	$body .= "<td><b>Сумма, euro</b></td>";
  $body .= "</tr>";

  if ($arrUser[1] == 'm') {
	  $manager_id = $arrUser[0];
    $where_main = " and dogovor.manager_id='".$manager_id."'";
  }

/*
  if ($cgi["search_manager_id"]) {
    $arrWhere[] = $table.".manager_id='".$cgi["search_manager_id"]."'";
  }
  if ($cgi["search_invite"]) {
    $arrWhere[] = $table.".is_invite='".$cgi["search_invite"]."'";
  }
  if ($cgi["search_work_type"]) {
    $arrWhere[] = $table.".work_type like '%:".$cgi["search_work_type"].":%'";
  }
*/
  if ($cgi["search_number"]) {
    $arrWhere[] = $table.".number = '".$cgi["search_number"]."'";
  }

	if($cgi[oldOrdCol]){
		if ($cgi[oldOrdCol] == 'name') {
			$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
		}	else {
			$order = " order by ".$cgi[oldOrdCol]." ".$cgi[ordDesc];
		}
  } else {
    $order = " order by $table.date desc";
  }
  if (count($arrWhere)) {
		$where = "and ".join(" and ",$arrWhere);
	}
  if ($cgi["btn_all"]) {
		$where = "";
	}

  $i = 0;
	$sql = "select $table.*, dogovor.id as d_id, dogovor.number as d_num, company.name as c_name from $table, dogovor, company where company.id = dogovor.client_id and dogovor.id = $table.dogovor_id and dogovor.exhibition_id=".$this_project." $where_main $where $order";
#		echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top>";
    $body .= "<td align=center>".++$i.".</td>";
    if ($arrUser[1] == 'b' || $arrUser[1] == 'a') {
      $body .= "<td align=center nowrap>";
      $body .= "<a href='$PHP_SELF?del_row=yes&num_str=".$row['id']."' onclick=\"return confirm('Вы, действительно хотите удалить позицию?')\"><img src='/images/del.gif' border=0></a> "; 
      $body .= "</td>";
    }
    $body .= "<td align=center><a href='$PHP_SELF?page=upd&num_str=".$row['id']."'>".$row[name]."</a>&nbsp;</td><td>".mysql2date2($row[date])."&nbsp;</td><td>".stripslashes($row[c_name])." (договор № ".$row[d_num].")</td>";
    if ($row[currency_id] == 2) {
      $body .= "<td>&nbsp;</td>";
      $body .= "<td>".$row[summ]."&nbsp;</td>";
      $all_summ_euro += $row[summ];
    } else {
      $body .= "<td>".$row[summ]."&nbsp;</td>";
      $body .= "<td>&nbsp;</td>";
      $all_summ_rub += $row[summ];
    }
    $body .= "</tr>\n"; 
    
	}
  $body .= "<tr valign=top><td align=right colspan=5><b>Итого:</b> </td><td>$all_summ_rub&nbsp;</td><td>$all_summ_euro&nbsp;</td></tr>\n";
	$body .= "</table>\n";
  return $body;
}

function do_search_here() {
	global $cgi, $table, $lists, $arrUser;
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
    $cgi["search_name"] = "";
  } elseif (!isset($cgi["search_manager_id"]) && $arrUser[1] == 'm') {
    $cgi["search_manager_id"] = $arrUser[0];
  }

  $cont .= "<tr><td><b>Номер</b>&nbsp;&nbsp;</td><td><input name=search_name value='".$cgi["search_name"]."' size='10'></tr>\n";

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
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}
?>