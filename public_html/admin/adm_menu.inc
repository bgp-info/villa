<?php
/* adm_menu.inc */

include_once("menu.inc");

$objAdmMenu = new AdmMenu();

#$objAdmMenu->arNodes[] = array('name' => 'Вход', 'href' => 'index.php');
$objAdmMenu->arNodes[] = array('name' => 'Выход', 'href' => 'exit.php');
#$objAdmMenu->arNodes[] = array('name' => 'Компании', 'href' => 'adm_company.php');
$objAdmMenu->arNodes[] = array('name' => 'Сотрудники', 'href' => 'adm_manager.php');
#$objAdmMenu->arNodes[] = array('name' => 'Типы поставщиков', 'href' => 'adm_type_postav.php');


$objAdmMenu->arNodes[] = array('name' => 'Страны', 'href' => 'adm_country.php');
$objAdmMenu->arNodes[] = array('name' => 'Города', 'href' => 'adm_city.php');
$objAdmMenu->arNodes[] = array('name' => 'Проекты', 'href' => 'adm_exhibition.php');
$objAdmMenu->arNodes[] = array('name' => 'Разделы прайс-листа', 'href' => 'adm_razdel_price.php');
$objAdmMenu->arNodes[] = array('name' => 'Виды деятельности', 'href' => 'adm_work_type.php');
#$objAdmMenu->arNodes[] = array('name' => 'Информация', 'href' => 'adm_data.php');

#$objAdmMenu->arNodes[] = array('name' => 'Сектора', 'href' => 'adm_sektor.php');
#$objAdmMenu->arNodes[] = array('name' => 'Стенды', 'href' => 'adm_stend.php" id="stend');

#$objAdmMenu->arNodes[] = array('name' => 'Тематика', 'href' => 'adm_subject.php');
#$objAdmMenu->arNodes[] = array('name' => 'Виды спорта', 'href' => 'adm_sport.php');
#$objAdmMenu->arNodes[] = array('name' => 'Бренды', 'href' => 'adm_brend.php');
#$objAdmMenu->arNodes[] = array('name' => 'Должности', 'href' => 'adm_dolzhnost.php');
#$objAdmMenu->arNodes[] = array('name' => 'Валюта', 'href' => 'adm_currency.php');

#$objAdmMenu->arNodes[] = array('name' => 'Операторы', 'href' => 'adm_operator_old.php');
#$objAdmMenu->arNodes[] = array('name' => 'Поставщики', 'href' => 'adm_postav_old.php');
?>