<?
# ������� ������������ �������� �������� � ������ (��������) 

$numnames=array();

$numnames["1"]="����";
$numnames["2"]="���";
$numnames["3"]="���";
$numnames["4"]="������";
$numnames["5"]="����";
$numnames["6"]="�����";
$numnames["7"]="����";
$numnames["8"]="������";
$numnames["9"]="������";
$numnames["10"]="������";
$numnames["11"]="�����������";
$numnames["12"]="����������";
$numnames["13"]="����������";
$numnames["14"]="������������";
$numnames["15"]="����������";
$numnames["16"]="�����������";
$numnames["17"]="����������";
$numnames["18"]="������������";
$numnames["19"]="������������";
$numnames["20"]="��������";
$numnames["30"]="��������";
$numnames["40"]="�����";
$numnames["50"]="���������";
$numnames["60"]="����������";
$numnames["70"]="���������";
$numnames["80"]="�����������";
$numnames["90"]="���������";
$numnames["100"]="���";
$numnames["200"]="������";
$numnames["300"]="������";
$numnames["400"]="���������";
$numnames["500"]="�������";
$numnames["600"]="��������";
$numnames["700"]="�������";
$numnames["800"]="���������";
$numnames["900"]="���������";
$numnames["1000"] ="������";
$numnames["1000s"]="�����";
$numnames["1000i"]="������";
$numnames["1001"] ="����";
$numnames["1002"] ="���";
$numnames["1m"]   ="�������";
$numnames["1mv"]  ="���������";
$numnames["1ma"]  ="��������";
$numnames["1b"]   ="��������";
$numnames["1bv"]  ="����������";
$numnames["1ba"]  ="���������";

$replace=Array(
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�", "�" => "�", "�" => "�",
  "�" => "�"
);

function num_to_string($num){
  global $c, $l, $p, $userid, $numnames, $replace;
   if(strlen($num)<=12){
    settype($num,"string");
    $num1="";
    for ($i = 0; $i <= strlen($num); $i++) {
      $num1.= substr ($num, (strlen($num)-$i), 1);
    } $num=$num1;
###  # ���������
    $n3 = substr ($num, 11, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # ����� ���������

    $n=""; $n1=""; $n = substr ($num, 10, 1); $n1 = substr ($num, 9, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # ������� ���������
       $numstr.=$numnames[$n1]; $numstr.=" ";  # ������� ���������
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # ������� ���������
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # ������� ���������
    } elseif ($n1){
       $numstr.=$numnames[$n1]; $numstr.=" ";  # ������� ���������
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

###  # ��������
    $n3=""; $n3 = substr ($num, 8, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # ����� ���������

    $n=""; $n1=""; $n = substr ($num, 7, 1); $n1 = substr ($num, 6, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # ������� ���������
       $numstr.=$numnames[$n1]; $numstr.=" ";  # ������� ���������
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # ������� ���������
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # ������� ���������
    } elseif ($n1){
       $numstr.=$numnames[$n1]; $numstr.=" ";  # ������� ���������
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

###  # ������
    $n3=""; $n3 = substr ($num, 5, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # ����� �����

    $n=""; $n1=""; $n = substr ($num, 4, 1); $n1 = substr ($num, 3, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # ������� �����
       if($n1==1){
         $numstr.=$numnames["1001"]; $numstr.=" ";  # ������� �����
       } elseif ($n1==2){
         $numstr.=$numnames["1002"]; $numstr.=" ";  # ������� �����
       } else {
         $numstr.=$numnames[$n1]; $numstr.=" ";  # ������� �����
       }
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # ������� �����
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # ������� �����
    } elseif ($n1){
       if($n1==1){
         $numstr.=$numnames["1001"]; $numstr.=" ";  # ������� �����
       } elseif ($n1==2){
         $numstr.=$numnames["1002"]; $numstr.=" ";  # ������� �����
       } else {
         $numstr.=$numnames[$n1]; $numstr.=" ";  # ������� �����
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

###  # ����� ������� �������
    $n3=""; $n3 = substr ($num, 2, 1);
    if($n3){ $numstr.=$numnames[$n3."00"]; $numstr.=" "; } # �����

    $n=""; $n1=""; $n = substr ($num, 1, 1); $n1 = substr ($num, 0, 1);
    if($n && $n1 && "$n$n1">20 ){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # �������
       $numstr.=$numnames[$n1]; $numstr.=" ";  # �������
    } elseif($n && $n1 && "$n$n1"<21 ){
       $numstr.=$numnames["$n$n1"]; $numstr.=" ";  # �������
    } elseif ($n){
       $numstr.=$numnames[$n."0"]; $numstr.=" ";  # �������
    } elseif ($n1){
       $numstr.=$numnames[$n1]; $numstr.=" ";  # �������
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
