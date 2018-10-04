<?php
/* menu.inc */

class AdmMenu{
  var $arNodes = array();

  function AdmMenu(){

  }

  function GetMenu($user_id, $node){
    global $cgi;
    $arrUser = GetUserName($user_id);

		$query = "select * from manager where id=$user_id";
		$result = my_exec($query);
		$row = mysql_fetch_array($result);
    $cont='';
		$i=0;
    foreach($this->arNodes as $key => $val){
#			if (++$i == 10 || $i == 18) {$br = "<br>";} else {$br = "";}
      
      if (($arrUser[1] != $val['stop']) && (!$val['allow'] || $val['allow'] == $arrUser[1])) {
        if($node == $val['name']){
          $cont .= '[ <A HREF="'.$val['href'].'"><FONT COLOR="#000000"><B>'.
            $val['name'].'</B></FONT></a> ] '.$br; 
        }else{
          $cont .= '[ <A HREF="'.$val['href'].'">'.$val['name'].'</a> ] '.$br;
        }
      }
    }
    return $cont;
  }
}


?>