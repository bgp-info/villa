<?php
/* adm_menu.inc */

include_once("menu.inc");

$objAdmMenu = new AdmMenu();

#$objAdmMenu->arNodes[] = array('name' => '����', 'href' => 'index.php');
#$objAdmMenu->arNodes[] = array('name' => '�����', 'href' => 'exit.php');
$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'company.php');
$objAdmMenu->arNodes[] = array('name' => '�������� � ������������', 'href' => 'dogovor.php');
$objAdmMenu->arNodes[] = array('name' => '�����', 'href' => 'schet_new.php');
$objAdmMenu->arNodes[] = array('name' => '�������', 'href' => 'plateg_new.php');
$objAdmMenu->arNodes[] = array('name' => '���. ������ � ������������', 'href' => 'stat_service.php');
$objAdmMenu->arNodes[] = array('name' => '���������', 'href' => 'building.php');
$objAdmMenu->arNodes[] = array('name' => '�������', 'href' => 'finans.php');
$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'task_new.php', 'stop' => 'b');
#$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'statistic.php', 'allow' => 'a');
$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'picture.php');
$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'report.php', 'allow' => 'a');
$objAdmMenu->arNodes[] = array('name' => '�����', 'href' => 'table_friz.php');
$objAdmMenu->arNodes[] = array('name' => '�������� ���������', 'href' => 'table_check.php');

#$objAdmMenu->arNodes[] = array('name' => '������� ������������', 'href' => 'all_rashod.php');
#$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'all_public.php');
#$objAdmMenu->arNodes[] = array('name' => '������', 'href' => 'all_stend.php');
#$objAdmMenu->arNodes[] = array('name' => '������������', 'href' => 'all_spec.php');
#$objAdmMenu->arNodes[] = array('name' => '�������', 'href' => 'all_badg.php');


#$objAdmMenu->arNodes[] = array('name' => '����������', 'href' => 'adm_postav_old.php');
?>