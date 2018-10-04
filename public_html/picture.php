<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");

if (!$user_id = authenticateUser($cookie_login, $cookie_passwd)) {
	header("Location:http://$HTTP_HOST/index.php");
	exit();
}

#print_arr($cgi);

$text_menu = $objAdmMenu->GetMenu($user_id, 'Планировки');
$arrUser = GetUserName($user_id);
$manager = "<font color=990000>".$lists['arrTypeMan'][$arrUser[1]]." - ".$arrUser[2]."</font>";

$default = make_filter($user_id);

show(); 

include_once("usr_templ.php");


#######################################################################


function show() {
  global $body, $table, $cgi, $arrUser, $lists, $this_project;
#  print_arr($arrUser);

#	$body = do_search_here();

  $body .= "<br><br>";

	$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>";
  $body .= "<tr valign=top align=center class=tr1>";
	$body .= "<td><b>Название проекта</b></td>";
	$body .= "<td><b>Даты проведения</b></td>";
	$body .= "<td><b>Планировки</b></td>";
  $body .= "</tr>";

  $where_main = " and id=".$this_project;


	$sql = "select * from exhibition where 1 $where_main order by id desc";
#			echo $sql;
	$sth = my_exec($sql);
	while($row = mysql_fetch_array($sth)) {
		$body .= "<tr valign=top align=center>";
    $body .= "<td>".$row[name]."&nbsp;</td>\n"; 
    $body .= "<td>".mysql2date2($row[date_start])." - ".mysql2date2($row[date_stop])."&nbsp;</td>\n"; 
#    $body .= "<td><a href='#' onClick=\"return w_o('/show_pic.php?id=".$row[id]."',300,300);\"><img src='/images/eye.gif' border=0 hspace=8></a></td>";
    $body .= "<td align=left>";
    $sql = "select * from exhibition_plan where exhibition_id='".$row['id']."'";
    $sth_pl = my_exec($sql);

    $arrPic = array();
    while($row_pl = mysql_fetch_array($sth_pl)) {
      if ($row_pl['ext']=='---') {
        $arrPic[] = '&nbsp;';
      } else {
        if ($row_pl['ext']=='.pdf') {
          $arrPic[] = "<a href='/picture_plan/pic_".$row_pl[id].$row_pl[ext]."' target='_blank'>".$row_pl[name]."</a>";
        } else {
          $arrPic[] = "<a href='#' onClick=\"return w_o('/show_pic_2.php?id=".$row_pl[id]."',300,300);\">".$row_pl[name]."</a>";
        }
      }
    }

    $body .= join("<br>",$arrPic);

    $body .= "&nbsp;</td>";
	}
  $body .= "</tr>";
	$body .= "</table>\n";

  return $body;
}


?>