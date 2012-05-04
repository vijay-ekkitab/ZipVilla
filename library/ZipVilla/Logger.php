<?php

class ZipVilla_Logger extends Zend_Log {

    public function __construct() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $logdir = $config->zipvilla->logdir;
        $logfile = $config->zipvilla->logfile;
        $logfile = $logdir . DIRECTORY_SEPARATOR . $logfile;
        $writer = new Zend_Log_Writer_Stream($logfile);
        parent::__construct($writer);
    }
}

?>