<?php
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../application/models'),
    get_include_path(),
)));

include_once("Zend/Controller/Action/Helper/Abstract.php");
include_once("ZipVilla/ListingsImporter.php");
include_once("Attributes.php");
include_once("Zend/Registry.php");
include_once("Zend/Config/Ini.php");
include_once("Types.php");
include_once("ZipVilla/Utils.php");
include_once("Listings.php");
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("Enumerations.php");
include_once("ZipVilla/Helper/IndexManager.php");

$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
Zend_Registry::set('config', $config);

$options = getopt('ni:');
$infile = '';
$import = true;

if (!isset($options['i'])) {
    echo "Input file not specified.\n";
    echo "Usage: " . $argv[0] . " [-n] -i <listing csv file> \n";
    exit(1);
}
else {
    $infile = $options['i'];
}

if (isset($options['n'])) {
    echo "Test mode requested. Listings will NOT be imported.\n";
    $import = false;
}

$imp = new ListingsImporter($import);

$imp->importFile($infile);

?>
