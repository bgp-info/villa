<?php
/* menu.inc */

class AdmMenu{
  var $arNodes = array();

  function AdmMenu(){

  }

  function GetMenu($node){
    global $cgi;
    $cont='';
		$i=0;
    foreach($this->arNodes as $key => $val){
			if (++$i == 10 || $i == 18) {$br = "<br>";} else {$br = "";}
      if($node == $val['name']){
        $cont .= '[ <A HREF="'.$val['href'].'"><FONT COLOR="#000000"><B>'.
          $val['name'].'</B></FONT></a> ] '.$br; 
      }else{
        $cont .= '[ <A HREF="'.$val['href'].'">'.$val['name'].'</a> ] '.$br;
      }
    }
    return $cont;
  }
}


?>