<?php
/* adm_menu.inc */

include_once("menu.inc");

$objAdmMenu = new AdmMenu();

#$objAdmMenu->arNodes[] = array('name' => '����', 'href' => 'index.php');
$objAdmMenu->arNodes[] = array('name' => '�����', 'href' => 'exit.php');
#$objAdmMenu->arNodes[] = array('name' => '��������', 'href' => 'adm_company.php');
$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'adm_manager.php');
#$objAdmMenu->arNodes[] = array('name' => '���� �����������', 'href' => 'adm_type_postav.php');


$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'adm_country.php');
$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'adm_city.php');
$objAdmMenu->arNodes[] = array('name' => '�������', 'href' => 'adm_exhibition.php');
$objAdmMenu->arNodes[] = array('name' => '������� �����-�����', 'href' => 'adm_razdel_price.php');
$objAdmMenu->arNodes[] = array('name' => '���� ������������', 'href' => 'adm_work_type.php');
#$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'adm_data.php');

#$objAdmMenu->arNodes[] = array('name' => '�������', 'href' => 'adm_sektor.php');
#$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'adm_stend.php" id="stend');

#$objAdmMenu->arNodes[] = array('name' => '��������', 'href' => 'adm_subject.php');
#$objAdmMenu->arNodes[] = array('name' => '���� ������', 'href' => 'adm_sport.php');
#$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'adm_brend.php');
#$objAdmMenu->arNodes[] = array('name' => '���������', 'href' => 'adm_dolzhnost.php');
#$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'adm_currency.php');

#$objAdmMenu->arNodes[] = array('name' => '���������', 'href' => 'adm_operator_old.php');
#$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'adm_postav_old.php');
?>