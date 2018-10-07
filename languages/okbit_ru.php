<?php
/**
 * Russian language file for okbit udp module
 *
 * @package OkBit UDP 
 * @author <skysilver.da@gmail.com>
 * @copyright 2017-2018 Agaphonov Dmitri aka skysilver <skysilver.da@gmail.com> (c)
 * @localization Alex Sokolov aka Gelezako <admin@gelezako.com> http://blog.gelezako.com
 * @version 1.3b
 *
 **/

$dictionary = array(

'OKBIT_SCRIPT_NAME'=>'Название сценария',
'OKBIT_APP_ABOUT' => 'О модуле',
'OKBIT_APP_CLOSE' => 'Закрыть',
'OKBIT_TITLE' => 'Название',
'OKBIT_GATE_IP' => 'IP-Шлюза',
'OKBIT_VER' => 'Версия прошивки',
'OKBIT_SUB_ID' => 'ID-подсети',
'OKBIT_DEVICE' => 'ID-модуля',
'OKBIT_TIPE_DEVICE' => 'Тип модуля',
'OKBIT_STATUS' => 'Статус',
'OKBIT_APP_MODULE' => 'Модуль поддержки Шлюза OkBit.ru по протоколу UDP ',
'OKBIT_APP_PROJ' => 'Проект в',
'OKBIT_APP_DISCUS' => 'Обсуждение модуля на',
'OKBIT_APP_DISCUS2' => 'форуме',
'OKBIT_APP_DONATE' => 'Поддержать разработку и развитие модуля:',
'OKBIT_APP_DONATE2' => 'Внутренний счет в ',
'OKBIT_APP_Author' => 'Автор',
'OKBIT_APP_THANKS' => 'Благодарности',
'OKBIT_APP_NAME' => 'OkBit UDP',
'OKBIT_APP_CYCLE_START' => 'Цикл запущен',
'OKBIT_APP_CYCLE_STOP' => 'Цикл остановлен',
'OKBIT_APP_IP' => 'IP сервера MajorDoMo ',
'OKBIT_APP_IP_TOOLTIP' => 'Необходимо указать для корректной работы на сервере с двумя и более сетевыми интерфейсами.',
'OKBIT_APP_DEBUG' => 'Отладка ',
'OKBIT_APP_DEBUG_TOOLTIP' => 'Запись обмена сообщениями между сервером и устройствами в отдельный лог-файл YYYY-mm-dd_xiaomimiio.log. Для вступления изменений в силу требуется перезапустить цикл.',
'OKBIT_APP_DEBUG_TOOLTIP2' => 'Запись отладочных сообщений библиотеки miIO-Lib в лог-файл цикла log_YYYY-mm-dd-cycle_xiaomimiio.php.txt. Для вступления изменений в силу требуется перезапустить цикл.',
'OKBIT_APP_DEBUG_TOOLTIP3' => 'Запись отладочных сообщений цикла в лог-файл log_YYYY-mm-dd-cycle_xiaomimiio.php.txt. Для вступления изменений в силу требуется перезапустить цикл.',
'OKBIT_APP_ONLINE' => 'Устройство в сети',
'OKBIT_APP_OFFLINE' => 'Устройство не доступно',
'OKBIT_APP_DELETE' => 'Устройство удалено',
'OKBIT_APP_OPTIONS' => 'Параметры',
'OKBIT_APP_CURRENT' => 'Получить актуальное состояние',
'OKBIT_APP_ADD_GATE' => 'Добавить шлюз',
'OKBIT_APP_FIND_GATE' => 'Поиск шлюза',
'OKBIT_APP_ADD_DEVICE' => 'Добавить модуль',
'OKBIT_APP_FIND_DEVICES' => 'Поиск модулей'

);

foreach ($dictionary as $k=>$v)
{
   if (!defined('LANG_' . $k))
   {
      define('LANG_' . $k, $v);
   }
}

?>
