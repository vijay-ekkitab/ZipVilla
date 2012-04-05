<?php
define('APPLICATION_PATH', '/var/www/z/application');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
set_include_path(implode(PATH_SEPARATOR, array('/var/www/z/library', '/var/www/z/application/models', get_include_path())));

include_once("Users.php");
include_once("Zend/Registry.php");
include_once("Zend/Config/Ini.php");

$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
Zend_Registry::set('config', $config);

$options = getopt('u:p:');

if ((!isset($options['u'])) || (!isset($options['p']))) {
    echo "Either username or password (or both) is not specified.\n";
    echo "Usage: " . $argv[0] . "  -u <userid> -p <password> \n";
    exit(1);
}

$userid = $options['u'];
$password = $options['p'];

$user = Application_Model_Users::findOne(array('emailid' => $userid));

if ($user == null) {
    echo "[Error] Could not find user '$userid'. No password update is done.\n";
}
else {
    $user->password = $password;
    $user->save();
    echo "Password for user '$userid' has been updated.\n";
}
?>
