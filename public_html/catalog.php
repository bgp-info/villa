<?php

include_once("usr_menu.inc");
include_once("../private/conf.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

if(isset($cgi['return_dogovor'])) {
  header("Location:http://$HTTP_HOST/dogovor.php?page=upd&num_str=".$cgi['num_str']);
	exit();
}

#print_arr($cgi);

$text_menu = $objAdmMenu->GetMenu($user_id, 'Договора с экспонентами');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

$table = "catalog";


if(isset($cgi['upd_row'])) {
  $sql_old = "select * from $table where dogovor_id=".$cgi['num_str'];
  $sth_old = my_exec($sql_old);
  $row_old = mysql_fetch_array($sth_old);
  $text_old = "";
  $text_old .= "Договор = ".$row_old[dogovor_id]."<br>";
  $text_old .= "Буква публикации = ".$row_old[letter]."<br>";
  $text_old .= "Статья на русском языке = ".$row_old[text]."<br>";
  $text_old .= "Статья на английском языке = ".$row_old[text_eng]."<br>";

	$sql = "update $table set letter = '".addslashes($cgi[letter])."', text = '".addslashes($cgi[text])."', text_eng = '".addslashes($cgi[text_eng])."' where dogovor_id=".$cgi['num_str'];
	my_exec($sql);

  $text_new = "Буква публикации = ".addslashes($cgi[letter])."<br>Статья на русском языке = ".addslashes($cgi[text])."<br>Статья на английском языке = ".addslashes($cgi[text_eng]);
  $query = "insert into statistic set user_id=$user_id, date=now(), type='update', text_old='".$text_old."', text_new='".$text_new."', text_sql='".addslashes($sql)."', comment='Обновлен каталог'";
  my_exec($query);
}


add($cgi['num_str']);      

include_once("usr_templ.php");


#######################################################################


function add($num_str) {
  global $filds, $body, $table;

  $sql = "select * from $table where dogovor_id='".$num_str."'";
  #		echo $sql;
  $sth = my_exec($sql);
  if (!mysql_num_rows($sth)) {
    $sql = "insert into $table set dogovor_id=$num_str";
    my_exec($sql);
  } else {
    $row = mysql_fetch_array($sth);
  }
  $header = "Редактирование публикации";
  $name_btn = "upd_row";
  $text_btn = "Обновить";
  
	$body = "<center><h4>$header</h4>";


  $body .= "<form method=post enctype='multipart/form-data'>\n";
	$body .= "<input type=hidden name=num_str value=$num_str>\n";
	$body .= "<table class=small width=600 border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
	$body .= "<tr valign=top><td width=200><b>Буква публикации</b></td><td><input name='letter' value='".$row[letter]."' size=3></td></tr>\n";
	$body .= "<tr valign=top><td colspan=2><b>Статья на русском языке</b>&nbsp;&nbsp;&nbsp;<font color=555555><i>символов: <b><span name=c1 id=c1></span></b></i></font><br><textarea name='text' id='text' cols=80 rows=15 onkeyup=countt(1);>".stripslashes($row[text])."</textarea></td></tr>\n";
	$body .= "<tr valign=top><td colspan=2><b>Статья на английском языке</b>&nbsp;&nbsp;&nbsp;<font color=555555><i>символов: <b><span name=c2 id=c2></span></b></i></font><br><textarea name='text_eng' id='text_eng' cols=80 rows=15 onkeyup=countt(2);>".stripslashes($row[text_eng])."</textarea></td></tr>\n";

	$body .= "</table><BR>\n";
  $body .= "<input type=submit name=$name_btn value='  $text_btn  '>\n";
  $body .= "<input type=submit name=return_dogovor value='Вернуться к договору'>\n";
  $body .= "</form></center>\n";
  $body .= "
    <script langeage=JavaScript>
    if (document.getElementById) {
      cnt1 = document.getElementById('c1');
      cnt2 = document.getElementById('c2');
    } else if (document.all) {
      cnt1 = document.all['c1'];
      cnt2 = document.all['c2'];
    }
    function countt(f) {
    if (document.getElementById) {
      text1 = document.getElementById('text').value;
      text2 = document.getElementById('text_eng').value;
    } else if (document.all) {
      text1 = document.all['text'].value;
      text2 = document.all['text_eng'].value;
    }
      if (f == 1) {
        cnt1.innerHTML = text1.length;
      } 
      if (f == 2) {
        cnt2.innerHTML = text2.length;
      }
//      alert(f);
    }
    countt(1);
    countt(2);
    </script>

  ";
  return $body;
}


?>