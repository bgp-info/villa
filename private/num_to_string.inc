<?
# функция конвертирует цифровое значение в строку (прописью) 

$numnames=array();

$numnames["1"]="один";
$numnames["2"]="два";
$numnames["3"]="три";
$numnames["4"]="четыре";
$numnames["5"]="пять";
$numnames["6"]="шесть";
$numnames["7"]="семь";
$numnames["8"]="восемь";
$numnames["9"]="девять";
$numnames["10"]="десять";
$numnames["11"]="одиннадцать";
$numnames["12"]="двенадцать";
$numnames["13"]="тринадцать";
$numnames["14"]="четырнадцать";
$numnames["15"]="пятнадцать";
$numnames["16"]="шестнадцать";
$numnames["17"]="семнадцать";
$numnames["18"]="восемнадцать";
$numnames["19"]="девятнадцать";
$numnames["20"]="двадцать";
$numnames["30"]="тридцать";
$numnames["40"]="сорок";
$numnames["50"]="пятьдесят";
$numnames["60"]="шестьдесят";
$numnames["70"]="семьдесят";
$numnames["80"]="восемьдесят";
$numnames["90"]="девяносто";
$numnames["100"]="сто";
$numnames["200"]="двести";
$numnames["300"]="триста";
$numnames["400"]="четыреста";
$numnames["500"]="пятьсот";
$numnames["600"]="шестьсот";
$numnames["700"]="семьсот";
$numnames["800"]="восемьсот";
$numnames["900"]="девятьсот";
$numnames["1000"] ="тысяча";
$numnames["1000s"]="тысяч";
$numnames["1000i"]="тысячи";
$numnames["1001"] ="одна";
$numnames["1002"] ="две";
$numnames["1m"]   ="миллион";
$numnames["1mv"]  ="миллионов";
$numnames["1ma"]  ="миллиона";
$numnames["1b"]   ="миллиард";
$numnames["1bv"]  ="миллиардов";
$numnames["1ba"]  ="миллиарда";

$replace=Array(
  "а" => "А", "б" => "Б", "в" => "В",
  "г" => "Г", "д" => "Д", "е" => "Е",
  "ж" => "Ж", "з" => "З", "и" => "И",
  "к" => "К", "л" => "Л", "м" => "М",
  "н" => "Н", "о" => "О", "п" => "П",
  "р" => "Р", "с" => "С", "т" => "Т",
  "у" => "У", "ф" => "Ф", "х" => "Х",
  "ц" => "Ц", "ч" => "Ч", "ш" => "Ш",
  "щ" => "Щ", "э" => "Э", "ю" => "Ю",
  "я" => "Я"
);

function num_to_string($num){
  global $c, $l, $p, $userid, $numnames, $replace;
   if(strlen($num)<=12){
    settype($num,"string");
    $num1="";
    for ($i = 0; $i <= strlen($num); $i++) {
      $num1.= substr ($num, (strlen($num)-$i), 1);
    } $num=$num1;
###  # Миллиарды
    $n3 = substr ($num, 11, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # сотни милиардов

    $n=""; $n1=""; $n = substr ($num, 10, 1); $n1 = substr ($num, 9, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки милиардов
       $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы милиардов
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # десятки милиардов
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки милиардов
    } elseif ($n1){
       $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы милиардов
    }

    if(strlen($num)>9 && (($n1 && $n1<10) || ($n && $n<10) || ($n3 && $n3<10))){
    if($n1==1 && $n!=1){
       $numstr.=$numnames["1b"]; $numstr.=" ";
    } elseif (($n1==0 || $n1>4) || $n==1 ){
       $numstr.=$numnames["1bv"]; $numstr.=" ";
    } else {
       $numstr.=$numnames["1ba"]; $numstr.=" ";
    }
    }

###  # Миллионы
    $n3=""; $n3 = substr ($num, 8, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # сотни миллионов

    $n=""; $n1=""; $n = substr ($num, 7, 1); $n1 = substr ($num, 6, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки миллионов
       $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы миллионов
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # десятки миллионов
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки миллионов
    } elseif ($n1){
       $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы миллионов
    }

    if(strlen($num)>6 && (($n1 && $n1<10) || ($n && $n<10) || ($n3 && $n3<10)) ){
    if($n1==1 && $n!=1){
       $numstr.=$numnames["1m"]; $numstr.=" ";
    } elseif (($n1==0 || $n1>4) || $n==1 ){
       $numstr.=$numnames["1mv"]; $numstr.=" ";
    } else {
       $numstr.=$numnames["1ma"]; $numstr.=" ";
    }
    }

###  # Тысячи
    $n3=""; $n3 = substr ($num, 5, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # сотни тысяч

    $n=""; $n1=""; $n = substr ($num, 4, 1); $n1 = substr ($num, 3, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки тысяч
       if($n1==1){
         $numstr.=$numnames["1001"]; $numstr.=" ";  # единицы тысяч
       } elseif ($n1==2){
         $numstr.=$numnames["1002"]; $numstr.=" ";  # единицы тысяч
       } else {
         $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы тысяч
       }
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # десятки тысяч
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки тысяч
    } elseif ($n1){
       if($n1==1){
         $numstr.=$numnames["1001"]; $numstr.=" ";  # единицы тысяч
       } elseif ($n1==2){
         $numstr.=$numnames["1002"]; $numstr.=" ";  # единицы тысяч
       } else {
         $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы тысяч
       }
    }

    if(strlen($num)>3 && (($n1 && $n1<10) || ($n && $n<10) || ($n3 && $n3<10)) ){
    if($n1==1 && $n!=1){
       $numstr.=$numnames["1000"]; $numstr.=" ";
    } elseif (($n1==0 || $n1>4) || $n==1 ){
       $numstr.=$numnames["1000s"]; $numstr.=" ";
    } else {
       $numstr.=$numnames["1000i"]; $numstr.=" ";
    }
    }

###  # Сотни единицы десятки
    $n3=""; $n3 = substr ($num, 2, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # сотни

    $n=""; $n1=""; $n = substr ($num, 1, 1); $n1 = substr ($num, 0, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки
       $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # десятки
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # десятки
    } elseif ($n1){
       $numstr.=$numnames[$n1]; $numstr.=" ";  # единицы
    }
 
    if (preg_match("/(^.)/",$numstr,$match)){
      return ereg_replace("(^.)",$replace[$match[1]],$numstr);
    } else {
      return $numstr;
    }
  } else {
    return "Number > 999 999 999 999 !";
  }
}
?>
