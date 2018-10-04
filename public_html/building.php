<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

$path_to_file = "/web/sites/mosc.ru/sub_domains/exhibition/www/picture_stend/";

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

if (isset($cgi['send_email_to_this_address'])) {
  if ($cgi['send_email_to_this_address']) {
    #print_arr($cgi);

    $sql = "select name from exhibition where id = '".$this_project."'";  
    $sth = my_exec($sql);
    $project_name =  stripslashes(mysql_result($sth,0));
    $subject = "Утвердите, пожалуйста, стенд";

    $sql = "select dogovor.*, stend.number as s_numver, stend.friz as s_friz, stend.ext, company.name as c_name, company.name_full as c_name_full, company.contakt, company.contakt_fio from dogovor left join stend on stend.dogovor_id = dogovor.id left join company on dogovor.client_id = company.id where dogovor.id = '".$cgi['dogovor_id']."'";
#    echo $sql;
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);

    $mail_content = "<center><p align=centre><b>Подробная информация по экспоненту ".stripslashes($row[c_name])."</b></p><br>";
    $mail_content .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $mail_content .= "<tr valign=top><td height=26><b>Название экспонента</b></td><td>".stripslashes($row[c_name_full])."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>Номер договора</b></td><td>".make_num($row[number])."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>Площадь в павильоне</b></td><td>".$row[area_all]."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>№ стенда</b></td><td>".$row[s_numver]."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>Открытая площадь</b></td><td>".$row[area_open]."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>Стандартное оборудование</b></td><td>".$lists['arrEquipment'][$row[equipment_id]]."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>Надпись на фризе</b></td><td>".stripslashes($row[s_friz])."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>Контактное лицо</b></td><td>".stripslashes($row[contakt_fio])."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26><b>Координаты</b></td><td>".stripslashes($row[contakt])."&nbsp;</td></tr>\n";
    $mail_content .= "<tr valign=top><td height=26 colspan=2><b>Дополнительные услуги и оборудование </b></td></tr>\n";
    $mail_content .= "<tr valign=top><td colspan=2>";
    $mail_content .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
    $mail_content .= "<tr valign=top bgcolor=dddddd><td height=26><b>Название</b></td><td><b>Количество</b></td></tr>\n";
    
    $sql = "select price.name, service.col from service, price where price.id = service.service_id and dogovor_id = '".$row[id]."'";
    $sth_s = my_exec($sql);
    while ($row_s = mysql_fetch_array($sth_s)) {
      $mail_content .= "<tr valign=top><td height=26><b>".$row_s[name]."</b></td><td>".$row_s[col]."&nbsp;</td></tr>\n";
    }
    $mail_content .= "</table>\n";
    $mail_content .= "</td></tr>\n";
    if ($row[ext] && $row[ext] != '---') {
      $arrFile[0][name] = "pic_".$row[id].$row[ext];
      $type = substr($row[ext],1);
      $arrFile[0][type] = ($type=='pdf'?'application/pdf':'image/'.$type);
      #if ($type=='pdf') {
        $mail_content .= "<tr valign=top><td align=center colspan=2>Схему см. в приложении к письму!</td></tr>\n";
      #} else {
      #  $mail_content .= "<tr valign=top><td align=center colspan=2><img src='pic_".$row[id].$row[ext]."'></td></tr>\n";
      #}
    }
    $mail_content .= "</table><BR>\n";
    $mail_content .= "<p>Утверждаю _______________________ </p>\n";

    $mail_content .= "<br><hr><font color=#666666>Просьба не отвечать на письмо, нажатием кнопки \"Ответить\", т.к. письмо отправлено с технического адреса, и ваш ответ не будет прочитан!!!</font>\n";


    $semi_rand = md5(time()); 
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
    $html_headers = "From: ".$project_name."<subscribe@exhibition.mosc.ru>"; 
    $html_headers .= "\nMIME-Version: 1.0\n" . 
                    "Content-Type: multipart/mixed;\n" . 
                    " boundary=\"{$mime_boundary}\"";

    $html_message = "--{$mime_boundary}\n" . 
                    "Content-type: text/html; charset=windows-1251\n" . 
                    "Content-Transfer-Encoding: 7bit\n\n";
    $html_message .= $mail_content."\n\n"; 

    if ($arrFile) {
      foreach ($arrFile as $k=>$file_info) {

        $file = fopen($path_to_file.$file_info[name],'rb'); 
        $data = fread($file,filesize($path_to_file.$file_info[name])); 
        fclose($file); 
        $data = chunk_split(base64_encode($data)); 

        $html_message .= "--{$mime_boundary}\n" . 
                      "Content-Type: ".$file_info[type].";\n  name=".$file_info[name].";\n" . 
                      "Content-Disposition: attachment;\n  filename=".$file_info[name]."\n" . 
                      "Content-ID: ".$file_info[name]."\n" .
                      "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
      }
    }

    $html_message .= "--{$mime_boundary}--\n"; 
    mail($cgi['send_email_to_this_address'], $subject, $html_message, $html_headers); 
    $error2 = red2("Ok. Письмо отослано!");
  } else {
    $error2 = red2("Я не понял, а слать то, КУДА собираемся?");
  }
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
    if ($k == 'city_id') {
      $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$arrCity[$$k]."&nbsp;</td></tr>\n";
    } elseif ($k == 'manager_id') {
      $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$arrManager[$$k]."&nbsp;</td></tr>\n";
    } else {
      $body .= "<tr valign=top><td height=26><b>$v[0]</b></td><td>".$$k."&nbsp;</td></tr>\n";
    }
	}
	$body .= "</table><BR>\n";
  $body .= "<input type=button onClick=history.back() value='  Вернуться к списку  '>\n";
  $body .= "</form></center>\n";
  return $body;
}


function show() {
  global $body, $table, $cgi, $arrUser, $arrCompany, $lists, $this_project, $error2;
  #print_arr($arrUser);

  $sql = "select dogovor_id from service group by dogovor_id";
  $sth = my_exec($sql);
  $arrServ = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrServ[$row[dogovor_id]] = 'Y';   
  }

	$body = do_search_here();

  
 
  if ($cgi["search_client_id"]) { # Выбрали одну компанию...
    $sql = "select * from company where id = '".$cgi["search_client_id"]."'";
    $sth = my_exec($sql);
    $row = mysql_fetch_array($sth);
    $c_name = stripslashes($row[name_full]);
    $c_contakt = stripslashes($row[contakt]);
    $c_contakt_fio = stripslashes($row[contakt_fio]);

    $body .= $error2;
    $body .= "<center><p align=centre><b>Подробная информация по экспоненту ".stripslashes($row[name])."</b></p>";

    $sql = "select dogovor.*, stend.number as s_numver, stend.friz as s_friz, stend.ext from dogovor left join stend on stend.dogovor_id = dogovor.id where dogovor.client_id = '".$cgi["search_client_id"]."' and dogovor.exhibition_id = '".$this_project."'";
#    echo $sql;
    $sth = my_exec($sql);
    while ($row = mysql_fetch_array($sth)) {
      $body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";

      $body .= "<tr valign=top><td height=26 colspan=2 align=center><table border=0><tr><td><a href='print_building.php?id=".$row[id]."' target='_blank'>Версия для печати</a>&nbsp;&nbsp;&nbsp;</td><td><form name=f2 method=post action='/building.php?search_client_id=".$cgi["search_client_id"]."' style='margin:0px; padding:0px'><a href='#' onClick='document.f2.submit();'>Отослать на E-mail:</a> <input name=send_email_to_this_address><input type=hidden name=search_client_id value='".$cgi["search_client_id"]."'><input type=hidden name=dogovor_id value='".$row[id]."'></form></td></tr></table></td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Название экспонента</b></td><td>".$c_name."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Номер договора</b></td><td>".make_num($row[number])."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Площадь в павильоне</b></td><td>".$row[area_all]."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>№ стенда</b></td><td>".$row[s_numver]."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Открытая площадь</b></td><td>".$row[area_open]."&nbsp;</td></tr>\n";
#      $body .= "<tr valign=top><td height=26><b>Количество кВт</b></td><td>&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Стандартное оборудование</b></td><td>".$lists['arrEquipment'][$row[equipment_id]]."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Надпись на фризе</b></td><td>".stripslashes($row[s_friz])."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Контактное лицо</b></td><td>".$c_contakt_fio."&nbsp;</td></tr>\n";
      $body .= "<tr valign=top><td height=26><b>Координаты</b></td><td>".$c_contakt."&nbsp;</td></tr>\n";
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
        if ($row[ext] == '.pdf') {
          $body .= "<tr valign=top><td align=center colspan=2><a href='/picture_stend/pic_".$row[id].$row[ext]."' target='_blank'><img src='/images/pdf.jpg' border=0 hspace=10></a></td></tr>";
        } else {
          $body .= "<tr valign=top><td align=center colspan=2><img src='/picture_stend/pic_".$row[id].$row[ext]."'></td></tr>\n";
        }
      }
      $body .= "</table><BR>\n";
      $body .= "<p>Утверждаю _______________________ </p>\n";
    }

    $body .= "<form method=post>\n";
    $body .= "<input type=button onClick=window.location.href='/building.php' value='  Вернуться к списку  '>\n";
    $body .= "</form></center>\n";

  } else {
    $body .= "<br><br>";
    $body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
    $body .= "<tr valign=top align=center class=tr1>";

  #	$body .= "<td><b>№ договора</b></td>";
    if($cgi[oldOrdCol] == 'number'){
      if($cgi[ordDesc] == ' desc '){
        $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
      }else{
        $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
      }
    } else {
      $str = "";
    }
    $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='number'; document.form_search.submit();return false;\"><b><font color=white>№ договора</font></b></a>&nbsp;&nbsp;$str</td>";

  #	$body .= "<td><b>экспонент</b></td>";
    if($cgi[oldOrdCol] == 'client_id'){
      if($cgi[ordDesc] == ' desc '){
        $str = "<IMG SRC='/images/desc_order.gif'  BORDER=0>";
      }else{
        $str = "<IMG SRC='/images/asc_order.gif'  BORDER=0>";
      }
    } else {
      $str = "";
    }
    $body .= "<td><a href='#' onClick=\"document.form_search.ordCol.value='client_id'; document.form_search.submit();return false;\"><b><font color=white>экспонент</font></b></a>&nbsp;&nbsp;$str</td>";

    $body .= "<td><b>№ стенда</b></td>";
    $body .= "<td><b>фриз</b></td>";
    $body .= "<td><b>доп. услуги и оборудование</b></td>";
    $body .= "</tr>";

    $where_m = " and dogovor.exhibition_id=".$this_project;
    if ($arrUser[1] == 'm') {
      $where_m .= " and company.manager_id='".$arrUser[0]."'";
    }

    //if ($cgi['search_exhibition_id']) {
    //  $where_m .= " and dogovor.exhibition_id=".$cgi['search_exhibition_id'];
    //}



    if($cgi[oldOrdCol]){
      if ($cgi[oldOrdCol] == 'number') {
        $order = " order by dogovor.number ".$cgi[ordDesc];
      }	elseif ($cgi[oldOrdCol] == 'client_id') {
        $order = " order by c_name ".$cgi[ordDesc];
      }
    } else {
      $order = " order by dogovor.number desc";
    }

    if (count($arrWhere)) {
      $where = "and ".join(" and ",$arrWhere);
    }
    if ($cgi["btn_all"]) {
      $where = "";
    }
    $sql = "select dogovor.*, stend.number as s_number, stend.friz as s_friz, company.name as c_name from company, dogovor  left join stend on stend.dogovor_id = dogovor.id where company.id = dogovor.client_id and dogovor.status_id=0 $where_m $where $order";
  #			echo $sql;
    $sth = my_exec($sql);
    while($row = mysql_fetch_array($sth)) {
      $body .= "<tr valign=top>";
      $body .= "<td align=center><a href='/dogovor.php?page=info&num_str=".$row['id']."'>".make_num($row[number])."</a>&nbsp;</td>";
      $body .= "<td><a href='/building.php?search_client_id=".$row['client_id']."'>".stripslashes($row[c_name])."</a>&nbsp;</td><td>".$row[s_number]."&nbsp;</td><td align=center>".($row[s_friz]?"<img src='/images/galka.gif'>":"&nbsp;")."</td><td align=center>".($arrServ[$row['id']]?"<img src='/images/galka.gif'>":"&nbsp;")."&nbsp;</td></tr>\n"; 
    }
    $body .= "</table>\n";
  }
  return $body;
}

function do_search_here() {
	global $cgi, $table, $arrUser, $this_project;
  
  if ($arrUser[1] == 'm') {
    #$sql = "select id, name from company where manager_id='".$arrUser[0]."' order by name";
    $sql = "select company.id, company.name as c_name, dogovor.number from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' and company.manager_id='".$arrUser[0]."' order by company.name, dogovor.number";
  } else {
    #$sql = "select id, name from company order by name";
    $sql = "select company.id, company.name as c_name, dogovor.number from dogovor, company where company.id = dogovor.client_id and dogovor.exhibition_id = '".$this_project."' order by company.name, dogovor.number";
  }
  $sth = my_exec($sql);
  $arrCompany = array();
  while ($row = mysql_fetch_array($sth)) {
    $arrCompany[$row[id]] = stripslashes($row[c_name])." (договор № ".$row[number].")";
  }



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
    $cgi["search_client_id"] = "";
  }

  $cont .= "<tr><td><b>Экспонент</b>&nbsp;&nbsp;</td><td>".do_combobox('search_client_id',$arrCompany,$cgi["search_client_id"],'',1)."</td></tr>\n";

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
		default:
			$cont = "<input name=$name value='".$value."' size='".$lenght."'>";
  }
	return $cont;
}
?>