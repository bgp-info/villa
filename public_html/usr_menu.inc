<?php
/* adm_menu.inc */

include_once("menu.inc");

$objAdmMenu = new AdmMenu();

#$objAdmMenu->arNodes[] = array('name' => 'Вход', 'href' => 'index.php');
#$objAdmMenu->arNodes[] = array('name' => 'Выход', 'href' => 'exit.php');
$objAdmMenu->arNodes[] = array('name' => 'Экспоненты', 'href' => 'company.php');
$objAdmMenu->arNodes[] = array('name' => 'Договора с экспонентами', 'href' => 'dogovor.php');
$objAdmMenu->arNodes[] = array('name' => 'Счета', 'href' => 'schet_new.php');
$objAdmMenu->arNodes[] = array('name' => 'Платежи', 'href' => 'plateg_new.php');
$objAdmMenu->arNodes[] = array('name' => 'Доп. услуги и оборудование', 'href' => 'stat_service.php');
$objAdmMenu->arNodes[] = array('name' => 'Застройка', 'href' => 'building.php');
$objAdmMenu->arNodes[] = array('name' => 'Финансы', 'href' => 'finans.php');
$objAdmMenu->arNodes[] = array('name' => 'Задачи', 'href' => 'task_new.php', 'stop' => 'b');
#$objAdmMenu->arNodes[] = array('name' => 'Статистика', 'href' => 'statistic.php', 'allow' => 'a');
$objAdmMenu->arNodes[] = array('name' => 'Планировки', 'href' => 'picture.php');
$objAdmMenu->arNodes[] = array('name' => 'Отчеты', 'href' => 'report.php', 'allow' => 'a');
$objAdmMenu->arNodes[] = array('name' => 'Фризы', 'href' => 'table_friz.php');
$objAdmMenu->arNodes[] = array('name' => 'Контроль застройки', 'href' => 'table_check.php');

#$objAdmMenu->arNodes[] = array('name' => 'Расходы организатора', 'href' => 'all_rashod.php');
#$objAdmMenu->arNodes[] = array('name' => 'Публикации', 'href' => 'all_public.php');
#$objAdmMenu->arNodes[] = array('name' => 'Стенды', 'href' => 'all_stend.php');
#$objAdmMenu->arNodes[] = array('name' => 'Спецификация', 'href' => 'all_spec.php');
#$objAdmMenu->arNodes[] = array('name' => 'Баджики', 'href' => 'all_badg.php');


#$objAdmMenu->arNodes[] = array('name' => 'Поставщики', 'href' => 'adm_postav_old.php');
?>