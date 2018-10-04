<?
$root = $_SERVER['DOCUMENT_ROOT']; 
include_once ($root."/../private/conf.inc"); 

function print_arr($arr) {
  echo "<pre>";
  print_r($arr);
  echo "</pre>";
}

function red($str) {
    echo "<center><font color=red size=4><b>$str</b></font></center>";
}

function red2($str) {
    return "<center><font color=red size=4><b>$str</b></font></center>";
}

function my_exec($sql){
	$sth = mysql_query($sql) or die ("Error SQL:".$sql."<br>\n<B>".mysql_error()."</B>");
#	echo $sql."<br>";
	return $sth;
}

function date2mysql($dateStr){
  $date;
  if (preg_match("/^(\d{1,2})\-(\d{1,2})\-(\d{4})$/",$dateStr, $matches)){
    $date = $matches[3]."-".$matches[2]."-".$matches[1];
  }else{
    $date = $dateStr;
  }
  return $date; 
}

function mysql2date($dateStr){
	global $lists;
  $date;
  if (preg_match("/^(\d{4})\-(\d{1,2})\-(\d{1,2})$/",$dateStr, $matches)){
    $date = $matches[3]." ".$lists[arrDateMonth][(int)$matches[2]]." ".$matches[1]."г.";
  }else{
    $date = $dateStr;
  }
  return $date; 
}

function mysql2date2($dateStr){
	global $lists;
  $date;
  if (preg_match("/^(\d{4})\-(\d{1,2})\-(\d{1,2})$/",$dateStr, $matches)){
    $date = $matches[3].".".$matches[2].".".$matches[1];
  }else{
    $date = $dateStr;
  }
  return $date; 
}

function mysql2datetime($dateStr){
	global $lists;
  $date;
  if (preg_match("/^(\d{4})\-(\d{1,2})\-(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/",$dateStr, $matches)){
    $date = $matches[3].".".$matches[2].".".$matches[1]." ".$matches[4].":".$matches[5];
  }else{
    $date = $dateStr;
  }
  return $date; 
}

function do_input($name,$value,$type,$ref_1,$ref_2,$lenght,$first) {
#	echo "$name, $value, $type, $ref_1, $ref_2, $lenght, $first<br>";
	global $cgi,$lists;
	if (!$lenght) $lenght=70;
	switch($type){
		case 'STR':
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
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
			$cont = do_combobox($name,$arr,$value,'',$first);
			break;
		case 'DATE':
      $cont = "\n";
			if (ereg ("search_",$name,$reg)) { ################# поиск
        $name_d_1=$name."_d_1";
        $name_m_1=$name."_m_1";
        $name_y_1=$name."_y_1";
        if (isset($cgi[$name_d_1]) && !isset($cgi['btn_all'])) {
          $value_d_1=$cgi[$name_d_1];
          $value_m_1=$cgi[$name_m_1];
          $value_y_1=$cgi[$name_y_1];
        } else {
          /*
          $value_d_1=date("d");
          $value_m_1=date("m");
          $value_y_1=date("Y")-1;
          */
          ereg ("(^[0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$", $cgi['search_date_1_def'],$reg);
          $value_d_1=$reg[3];
          $value_m_1=$reg[2];
          $value_y_1=$reg[1];
        }

        $name_d_2=$name."_d_2";
        $name_m_2=$name."_m_2";
        $name_y_2=$name."_y_2";
        if (isset($cgi[$name_d_2]) && !isset($cgi['btn_all'])) {
          $value_d_2=$cgi[$name_d_2];
          $value_m_2=$cgi[$name_m_2];
          $value_y_2=$cgi[$name_y_2];
        } else {
          /*
          $value_d_2=date("d");
          $value_m_2=date("m");
          $value_y_2=date("Y")+1;
          */
          ereg ("(^[0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$", $cgi['search_date_2_def'],$reg);
          $value_d_2=$reg[3];
          $value_m_2=$reg[2];
          $value_y_2=$reg[1];
        }
				$cont .= " от ";
#        	echo $name_d_1.", ".$lists['arrDateDay'].", ".$value_d_1."<br>";
				$cont .= do_combobox($name_d_1,$lists['arrDateDay'],$value_d_1,'','')." ";
				$cont .= do_combobox($name_m_1,$lists['arrDateMonth'],$value_m_1,'','')." ";
				$cont .= do_combobox($name_y_1,$lists['arrDateYear'],$value_y_1,'','');
				$cont .= "&nbsp;&nbsp;&nbsp; до ";
				$cont .= do_combobox($name_d_2,$lists['arrDateDay'],$value_d_2,'','')." ";
				$cont .= do_combobox($name_m_2,$lists['arrDateMonth'],$value_m_2,'','')." ";
				$cont .= do_combobox($name_y_2,$lists['arrDateYear'],$value_y_2,'','');
			} else {     ################## ввод
        $name_d=$name."_d";
        $name_m=$name."_m";
        $name_y=$name."_y";
        if (ereg ("(^[0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$", $value,$reg)) {
          $value_d=$reg[3];
          $value_m=$reg[2];
          $value_y=$reg[1];
        } elseif($first) {
          $value_d='';
          $value_m='';
          $value_y='';
        } else {
          $value_d=date("d");
          $value_m=date("m");
          $value_y=date("Y");
        }

				$cont .= do_combobox($name_d,$lists['arrDateDay'],$value_d,'',$first)." ";
				$cont .= do_combobox($name_m,$lists['arrDateMonth'],$value_m,'',$first)." ";
				$cont .= do_combobox($name_y,$lists['arrDateYear'],$value_y,'',$first);
			}
			break;
		case 'CHECKBOX':
			$cont = do_combobox($name,$lists['arrCheck'],$value,'','');
       break;
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}

function do_combobox($name,$arr,$check,$ext,$first){
  $cont = "<select name='".$name."'".$ext.">\n";
	if($first) {$cont .= "<option value=''>---";}
  while(list($k,$v) = each($arr)){
    $cont .= "<option value='".$k."'";
    if ($check == $k){
      $cont .= " selected ";
    }
    $cont .= ">".$v."\n";
  }
  $cont .= "</select>\n";
  return $cont;
}

function do_search($filds) {
	global $cgi, $table;
	$show = 0;
	$cont = "<form name=form_search method=post>\n<table border=1 cellspacing=0 cellpadding=10 bordercolorlight=black bordercolordark=white align=center>\n";
	$cont .= "<tr><td align=center>";
	$cont .= "<table border=0>";
	while (list($k,$v) = each($filds)) {
      if ($cgi['btn_all']) {
        $value="";
      } else {
        $value=$cgi["search_".$k];
      }
      $cont .= "<tr><td><b>".$v[0]."</b>&nbsp;&nbsp;</td><td>".do_input("search_".$k,$value,$v[1],$v[2],$v[3],30,1)."</td></tr>\n";
			$show = 1;
	}
	$cont .= "</td></tr></table>\n";
	$cont .= "<input type=hidden name=ordCol value='".$cgi['ordCol']."'>";
	$cont .= "<input type=hidden name=oldOrdCol value='".$cgi['oldOrdCol']."'>";
	$cont .= "<input type=hidden name=ordDesc value='".$cgi['ordDesc']."'>";
	$cont .= "<br><input type=submit name=btn_all value='Все записи'>&nbsp;&nbsp;&nbsp;";
	$cont .= "<input type=submit name=btn_search value='Поиск'>";
	$cont .= "</td></tr></table></form>\n";

	if (!$show) $cont = "";
	return $cont;
}

function authenticateAdmin ($user, $password) {
	global $adm_aut;
#  echo "1 - ".$user.", ".$password."<br>";
#  echo "2 - ".$adm_aut['adm_login'].", ".$adm_aut['adm_passw']."<br>";
	if (($user == $adm_aut['adm_login']) && ($password == $adm_aut['adm_passw'])) 
		return 1;
	else
		return 0;
}

function authenticateUser ($user, $password) {
	$query = "select * from manager where email='$user'";
	$result = my_exec($query);
	if (($row = mysql_fetch_array($result)) && ($password == $row["pass"] && $password != "")) 
		return $row[id];
	else
		return 0;
}

function GetUserName($user_id) { 
	$query = "select * from manager where id=$user_id";
	$result = my_exec($query);
	$row = mysql_fetch_array($result);
	$arrUser=array($row[id],$row[type],$row[name]);
	return $arrUser;
}

function make_num($num){
  if ((int)$num < 10) {
    $num = "00".$num;
  } elseif ((int)$num < 100) {
    $num = "0".$num;
  }
  return $num; 
}

function make_filter($user_id){
  global $root, $cgi, $this_project;
	$file_default = $root."/default/man_$user_id.txt";
	if (isset($cgi['ex_default'])) {
		$fp = fopen ($file_default, "w");
		$text_default = "<"."? \$old_default='".$cgi[ex_default]."'; ?".">";
		fwrite ($fp, $text_default);
	}

	#print_arr($cgi);
	$sql = "select id, name from exhibition order by id desc";
	$result = my_exec($sql);
	$arr = array();
	while ($row = mysql_fetch_array($result)) {
    if (!$first_e) $first_e = $row[id];
		$arr[$row[id]] = $row[name];
	}
  #echo $file_default."<br>";
	if (file_exists($file_default)) {
    #echo "1<br>";
		include $file_default;
	} else {
    #echo "2<br>";
    $old_default = $first_e;
		$fp = fopen ($file_default, "w");
		$text_default = "<"."? \$old_default='".$old_default."'; ?".">";
		fwrite ($fp, $text_default);
    chmod($file_default, 0666);
  }
  $this_project = $old_default;
	return "фильтр: ".do_combobox('ex_default',$arr,$old_default,'onchange="form_default.submit();"',0);
}

function make_xml_old(){
  global $root, $lists;
  if (!file_exists($root."/xml/to_1c.flg")) {
    $f = fopen($root."/xml/to_1c.flg", "w");
    fwrite($f, "1");
    fclose($f);

    $xml_content = '<?xml version="1.0" encoding="windows-1251"?>'."\n";
    $xml_content .= '<Заголовок Назначение="Контрагенты и договоры">'."\n";
    $sql = "select * from company order by id";
    $sth = my_exec($sql);
    while($row = mysql_fetch_array($sth)) {
      $xml_content .= '<Контрагент  ИД = "'.replace_string($row[id]).'" НаименованиеКраткое = "'.replace_string($row[name]).'" НаименованиеПолное = "'.replace_string($row[name_full]).'" ЮрАдрес = "'.replace_string($row[address]).'" ФактАдрес = "'.replace_string($row[address_fakt]).'" ИНН = "'.replace_string($row[inn]).'" ОКПО = "" рсч = "'.replace_string($row[ras_schet]).'" Банк = "'.replace_string($row[bank]).'" Город = "" ксч = "'.replace_string($row[kor_schet]).'" БИК = "'.replace_string($row[bik]).'" >'."\n";
      $xml_content .= "  <Договоры>\n";
      $sql_d = "select * from dogovor where client_id = '".$row[id]."' order by id";
      $sth_d = my_exec($sql_d);
      while($row_d = mysql_fetch_array($sth_d)) {
        $xml_content .= '    <Договор ИД = "'.replace_string($row_d[id]).'" Номер = "'.replace_string(make_num($row_d[number])).'" Дата = "'.replace_string($row_d[date]).'" Валюта = "'.replace_string($lists['arrCurrency'][$row_d[currency_id]]).'" />'."\n";
      }
      $xml_content .= "  </Договоры>\n";
      $xml_content .= "</Контрагент>\n";
    }
    $xml_content .= "</Заголовок>\n";

    $f = fopen($root."/xml/to_1c.xml", "w");
    fwrite($f, $xml_content);
    fclose($f);

    unlink($root."/xml/to_1c.flg");
  }
}

function make_xml($table='', $num_str=''){
  global $root, $lists;
  if (file_exists($root."/xml/to_1c_fresh.flg")) { # Только что забрал!
    $sql = "update company set xml_flg = 'N'";
    my_exec($sql);
    $sql = "update dogovor set xml_flg = 'N'";
    my_exec($sql);
    if ($table && $num_str) {
      $sql = "update ".$table." set xml_flg = 'Y' where id = '".$num_str."'";
      my_exec($sql);
    }
    unlink($root."/xml/to_1c_fresh.flg");
  } 

  $xml_content = '<?xml version="1.0" encoding="windows-1251"?>'."\n";
  $xml_content .= '<Заголовок Назначение="Контрагенты и договоры">'."\n";
  $sql = "select * from company order by id";
  $sth = my_exec($sql);
  while($row = mysql_fetch_array($sth)) {
    $xml_content .= '<Контрагент  ИД = "'.replace_string($row[id]).'" НаименованиеКраткое = "'.replace_string($row[name]).'" НаименованиеПолное = "'.replace_string($row[name_full]).'" ЮрАдрес = "'.replace_string($row[address]).'" ФактАдрес = "'.replace_string($row[address_fakt]).'" ИНН = "'.replace_string($row[inn]).'" ОКПО = "" рсч = "'.replace_string($row[ras_schet]).'" Банк = "'.replace_string($row[bank]).'" Город = "" ксч = "'.replace_string($row[kor_schet]).'" БИК = "'.replace_string($row[bik]).'" >'."\n";
    $xml_content .= "  <Договоры>\n";
    $sql_d = "select * from dogovor where client_id = '".$row[id]."' order by id";
    $sth_d = my_exec($sql_d);
    while($row_d = mysql_fetch_array($sth_d)) {
      $xml_content .= '    <Договор ИД = "'.replace_string($row_d[id]).'" Номер = "'.replace_string(make_num($row_d[number])).'" Дата = "'.replace_string($row_d[date]).'" Валюта = "'.replace_string($lists['arrCurrency'][$row_d[currency_id]]).'" />'."\n";
    }
    $xml_content .= "  </Договоры>\n";
    $xml_content .= "</Контрагент>\n";
  }
  $xml_content .= "</Заголовок>\n";

  $f = fopen($root."/xml/to_1c_all.xml", "w");
  fwrite($f, $xml_content);
  fclose($f);

  $xml_content = '<?xml version="1.0" encoding="windows-1251"?>'."\n";
  $xml_content .= '<Заголовок Назначение="Контрагенты и договоры">'."\n";

  $sql_d = "select * from dogovor where xml_flg = 'Y' order by id";
  $sth_d = my_exec($sql_d);
  $arrC = array();
  while($row_d = mysql_fetch_array($sth_d)) {
    $arrC[$row_d[client_id]] = $row_d[client_id];
  }
  #print_arr($arrC);
  #echo "count = ".count($arrC);
  if (count($arrC)) {
    $sql = "select * from company where xml_flg = 'Y' or id in (".join(",",$arrC).") order by id";
  } else {
    $sql = "select * from company where xml_flg = 'Y' order by id";
  }
  $sth = my_exec($sql);
  while($row = mysql_fetch_array($sth)) {
    $xml_content .= '<Контрагент  ИД = "'.replace_string($row[id]).'" НаименованиеКраткое = "'.replace_string($row[name]).'" НаименованиеПолное = "'.replace_string($row[name_full]).'" ЮрАдрес = "'.replace_string($row[address]).'" ФактАдрес = "'.replace_string($row[address_fakt]).'" ИНН = "'.replace_string($row[inn]).'" ОКПО = "" рсч = "'.replace_string($row[ras_schet]).'" Банк = "'.replace_string($row[bank]).'" Город = "" ксч = "'.replace_string($row[kor_schet]).'" БИК = "'.replace_string($row[bik]).'" >'."\n";
    $xml_content .= "  <Договоры>\n";
    $sql_d = "select * from dogovor where client_id = '".$row[id]."' and xml_flg = 'Y' order by id";
    $sth_d = my_exec($sql_d);
    while($row_d = mysql_fetch_array($sth_d)) {
      $xml_content .= '    <Договор ИД = "'.replace_string($row_d[id]).'" Номер = "'.replace_string(make_num($row_d[number])).'" Дата = "'.replace_string($row_d[date]).'" Валюта = "'.replace_string($lists['arrCurrency'][$row_d[currency_id]]).'" />'."\n";
    }
    $xml_content .= "  </Договоры>\n";
    $xml_content .= "</Контрагент>\n";
  }
  $xml_content .= "</Заголовок>\n";

  $f = fopen($root."/xml/to_1c_fresh.xml", "w");
  fwrite($f, $xml_content);
  fclose($f);

}

function replace_string($contents){
  $contents = stripslashes($contents);
  $contents = htmlspecialchars($contents);
  #$contents = str_replace (' ','&nbsp;', $contents); 
  #$contents = str_replace ('©','&copy;', $contents); 
  #$contents = str_replace ('®','&reg;', $contents); 
  #$contents = str_replace ('™','&trade;', $contents);
  #$contents = str_replace ('€','&euro;', $contents); 
  #$contents = str_replace ('§','&sect;', $contents); 
  $contents = str_replace ('„','&quot;', $contents); 
  $contents = str_replace ('“','&quot;', $contents); 
  $contents = str_replace ('”','&quot;', $contents); 
  $contents = str_replace ('«','&quot;', $contents); 
  $contents = str_replace ('»','&quot;', $contents); 
  return $contents;
}

function masort(&$data, $sortby) {
  static $funcs = array();

  if (empty($funcs[$sortby])) {
      $code = "\$c=0;";
      foreach ($data as &$array) { break; }
      foreach (split(',', $sortby) as $key) {
          $key = trim($key);
          if (strlen($key)>5 && strtolower(substr($key, -5))==' desc') {
              $asc = false;
              $key = substr($key, 0, strlen($key)-5);
          } else {
              $asc = true;
          }

          if(is_numeric($array[$key])) {
              $code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] " . (($asc)?'<':'>') . " \$b['$key']) ? -1 : 1 )) ) return \$c;";
          } else {
              $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return " . (($asc)?'':'-') . "\$c;\n";
          }
      }
      $code .= 'return $c;';
      $func = $funcs[$sortby] = create_function('$a, $b', $code);
  } else {
      $func = $funcs[$sortby];
  }
  return uasort($data, $func);
}

?>