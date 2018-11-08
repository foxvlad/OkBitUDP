<?php
/**
 * Russian language file for okbit udp module
 *
 * @package OkBit UDP 
 * @author <roxvlad@yandex.ru>
 * @copyright 2018 Gulidov Vladislav aka foxvlad (c)
 * @version 0.2b
 *
 **/

$dictionary = array(

'OKBIT_SCRIPT_NAME'=>'The name of the script',
'OKBIT_APP_ABOUT' => 'About the module',
'OKBIT_APP_CLOSE' => 'Close',
'OKBIT_TITLE' => 'Title',
'OKBIT_GATE_IP' => 'IP-device',
'OKBIT_VER' => 'Firmware version',
'OKBIT_MOD' => 'Device type',
'OKBIT_SUB_ID' => 'Subnet ID',
'OKBIT_DEVICE' => 'ID-module',
'OKBIT_TIPE_DEVICE' => 'Module type',
'OKBIT_STATUS' => 'Status',
'OKBIT_APP_MODULE' => 'UDP Protocol support module for devices OkBit.ru',
'OKBIT_APP_PROJ' => 'Project in',
'OKBIT_APP_DISCUS' => 'Module discussion on ',
'OKBIT_APP_DISCUS2' => 'forum',
'OKBIT_APP_CONNECT' => 'Project in ',
'OKBIT_APP_DONATE' => 'Support the development and development of the module:',
'OKBIT_APP_DONATE2' => 'Internal account at   ',
'OKBIT_APP_Author' => 'Autor',
'OKBIT_APP_THANKS' => 'Thanks:',
'OKBIT_APP_NAME' => 'OkBit UDP',
'OKBIT_APP_CYCLE_START' => 'Cycle started',
'OKBIT_APP_CYCLE_STOP' => 'Cycle stopped',
'OKBIT_APP_IP' => 'IP of MajorDoMo server',
'OKBIT_APP_IP_TOOLTIP' => 'You must specify to work correctly on a server with two or more network interfaces.',
'OKBIT_APP_PERIOD' => 'Verification period',
'OKBIT_APP_PERIOD_TOOLTIP' => 'Period (in seconds) to get online status of devices',
'OKBIT_APP_DEBUG' => 'Debug',
'OKBIT_APP_DEBUG_TOOLTIP' => 'Record messaging between the server and devices in a separate log file YYYY-mm-dd_orbit.long. The cycle must be restarted for the changes to take effect.',
'OKBIT_APP_DEBUG_TOOLTIP2' => 'Record debug messages, the mpi library-Lib in the log file cycle log_YYYY-mm-dd-cycle_okbit.php.txt. The cycle must be restarted for the changes to take effect.',
'OKBIT_APP_ONLINE' => 'Device on network',
'OKBIT_APP_OFFLINE' => 'The device is not available',
'OKBIT_APP_DELETE' => 'Device is removed',
'OKBIT_APP_OPTIONS' => 'Parameter',
'OKBIT_APP_CURRENT' => 'To obtain the current status',
'OKBIT_APP_ADD_GATE' => 'Add device',
'OKBIT_APP_FIND_GATE' => 'Device search',
'OKBIT_APP_ADD_DEVICE' => 'Add module',
'OKBIT_APP_FIND_DEVICES' => 'Search modules',
'OKBIT_APP_INFO' => 'Information',
'OKBIT_APP_IP_SERVER' => 'IP-address ',
'OKBIT_APP_DEVICE_ONLINE' => 'Device on network',
'OKBIT_APP_DEVICE_OFFLINE' => 'The device is not available',
'OKBIT_APP_INFO_DEVICE' => 'Update device information',
'OKBIT_APP_BIND_GATE' => 'To bind the gateway to the server',
'OKBIT_APP_DEVICES_IN_GATE' => 'Devices on the gateway',
'OKBIT_APP_EDIT' => 'Edit',
'OKBIT_APP_DEITE' => 'Remove',
'OKBIT_APP_REMOVAL_REQUEST' => 'You sure? Please confirm.'
);

foreach ($dictionary as $k=>$v)
{
   if (!defined('LANG_' . $k))
   {
      define('LANG_' . $k, $v);
   }
}

?>
