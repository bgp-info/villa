<? 

include_once ("/web/sites/mosc.ru/sub_domains/exhibition/inc/conf.inc");

if (!file_exists("/web/sites/mosc.ru/sub_domains/exhibition/www/xml/from_1c.flg")) { echo "Файл с этими данными я уже загружал!<br>Давайте, будем внимательнее!"; exit; }
/*
if (@fopen("/web/sites/mosc.ru/sub_domains/exhibition/www/xml/from_1c.flg", "r")) {
echo "found file\n";

} else {
echo "no file\n";
die;
}
echo "3\n";
*/
$xml = file_get_contents("/web/sites/mosc.ru/sub_domains/exhibition/www/xml/from_1c.xml");
//считали файл =)
preg_match_all( "/\<Счет\s+ИДСчет=\"(.*?)\"\s+НомерСчет=\"(.*?)\"\s+ДатаСчет=\"(.*?)\"\s+ИДКонтрагент=\"(.*?)\"\s+ИДДоговор=\"(.*?)\"\s+Услуга=\"(.*?)\"\s+СуммаБезНДС=\"(.*?)\"\s+СтавкаНДС=\"(.*?)\"\s+СуммаНДС=\"(.*?)\"\s+Сумма=\"(.*?)\"\s+Активность=\"(.*?)\"\s+Валюта=\"(.*?)\"\s+КодВалюты=\"(.*?)\".*?\/\>/", $xml, $arrSchet);
preg_match_all( "/\<Платеж\s+ИДПлатежа=\"(.*?)\"\s+ДатаОперации=\"(.*?)\"\s+ИдКонтрагент=\"(.*?)\"\s+ИдДоговор=\"(.*?)\"\s+ИдСчет=\"(.*?)\"\s+НомерВх=\"(.*?)\"\s+ДатаВх=\"(.*?)\"\s+Сумма=\"(.*?)\"\s+Назначение=\"(.*?)\"\s+Валюта=\"(.*?)\"\s+КодВалюты=\"(.*?)\".*?\/\>/", $xml, $arrPlateg);

#echo "<pre>";
#print_r($arrSchet);

foreach($arrSchet[1] as $k=>$v) {
  #echo "$k = $v<br>";
  if ($arrSchet[11][$k] == 0) {
    $sql = "delete from schet where id_1c = '".$v."'";
    my_exec($sql);
  } else {
    $sql_test = "select id from schet where id_1c = '".$v."'";
    #echo $sql_test."<br>";
    $sth_test = my_exec($sql_test);
    #echo $sth_test." - ".mysql_num_rows($sth_test)."<br>";
    if(mysql_num_rows($sth_test)) {
      $sql = "update schet set number2 = '".$arrSchet[2][$k]."', date = '".$arrSchet[3][$k]."', dogovor_id = '".$arrSchet[5][$k]."', note = '".addslashes($arrSchet[6][$k])."', summ = '".$arrSchet[10][$k]."', currency_id = '".($arrSchet[13][$k]=='978'?2:1)."' where id_1c = '".$v."'";
      my_exec($sql);
    } else {
      $sql_test = "select exhibition_id from dogovor where id = '".$arrSchet[5][$k]."'";
      $sth_test = my_exec($sql_test);
      $exhibition_id = mysql_result($sth_test,0);

      $sql_test = "select schet.number from schet, dogovor where dogovor.id=schet.dogovor_id and dogovor.exhibition_id = ".$exhibition_id." order by schet.number desc limit 1";
      #echo $sql_test."<br>";
      $sth_test = my_exec($sql_test);
      if(mysql_num_rows($sth_test)) {
        $schet_number = mysql_result($sth_test,0) + 1;
      } else {
        $schet_number = 1;
      }

      $sql = "insert into schet set id_1c = '".$v."', number = '".$schet_number."', number2 = '".$arrSchet[2][$k]."', date = '".$arrSchet[3][$k]."', dogovor_id = '".$arrSchet[5][$k]."', note = '".addslashes($arrSchet[6][$k])."', summ = '".$arrSchet[10][$k]."', currency_id = '".($arrSchet[13][$k]=='978'?2:1)."'";
      my_exec($sql);
    }
  }
  #echo $sql."<br>";
}

#echo "<pre>";
#print_r($arrPlateg);


foreach($arrPlateg[1] as $k=>$v) {
  #ereg("^(.*)\/[0-9]+-[0-9]+-[0-9]+\/[0-9]+$",$v,$regs);
  #$name = $regs[1];
  $sql_test = "select id from plateg where id_1c = '".$v."'";
  $sth_test = my_exec($sql_test);
  if(mysql_num_rows($sth_test)) {
    $plateg_id = mysql_result($sth_test,0);
    $sql = "update plateg set name = '".$arrPlateg[6][$k]."', date = '".$arrPlateg[2][$k]."', dogovor_id = '".$arrPlateg[4][$k]."', summ = '".$arrPlateg[8][$k]."', currency_id = '".($arrPlateg[11][$k]=='978'?2:1)."', text = '".addslashes($arrPlateg[9][$k])."' where id_1c = '".$v."'";
    my_exec($sql);
  } else {
    $sql = "insert into plateg set id_1c = '".$v."', name = '".$arrPlateg[6][$k]."', date = '".$arrPlateg[2][$k]."', dogovor_id = '".$arrPlateg[4][$k]."', summ = '".$arrPlateg[8][$k]."', currency_id = '".($arrPlateg[11][$k]=='978'?2:1)."', text = '".addslashes($arrPlateg[9][$k])."'";
    my_exec($sql);
    $plateg_id = mysql_insert_id();
  }
  #echo $sql."<br>";
  if ($arrPlateg[5][$k]) {
    $sql_test = "select * from schet where id_1c = '".$arrPlateg[5][$k]."'";
    $sth_test = my_exec($sql_test);
    if(mysql_num_rows($sth_test)) {
      $row = mysql_fetch_array($sth_test);
      $schet_id = $row[id];
      $sql_test = "select * from plateg_schet where plateg_id = '".$plateg_id."' and schet_id = '".$schet_id."'";
      $sth_test = my_exec($sql_test);
      if(mysql_num_rows($sth_test)) {
        $row = mysql_fetch_array($sth_test);
        $sql = "update plateg_schet set summ = '".$arrPlateg[8][$k]."', is_all = 'N' where id = '".$row[id]."'";
        my_exec($sql);
      } else {
        $sql = "insert into plateg_schet set plateg_id = '".$plateg_id."', schet_id = '".$schet_id."', summ = '".$arrPlateg[8][$k]."', is_all = 'N'";
        my_exec($sql);
      }
      #echo $sql."<br>";
    }
  }
  
  #echo $plateg_id."<br>";

}

@unlink("/web/sites/mosc.ru/sub_domains/exhibition/www/xml/from_1c.flg");

echo "Загрузка прошла успешно!";

/*
echo "<pre>";
print_r($arrSchet);

echo "<pre>";
print_r($arrPlateg);
*/

function my_exec($sql){
	$sth = mysql_query($sql) or die ("Error SQL:".$sql."<br>\n<B>".mysql_error()."</B>");
#	echo $sql."<br>";
	return $sth;
}
?>