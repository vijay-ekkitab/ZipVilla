<?php

class ZipVilla_Logger extends Zend_Log {

    const LOGDIR = "/var/log/ZipVilla";
    const LOGFILE = "app.log";
    	
    public function __construct() {
        $logfile = self::LOGDIR . DIRECTORY_SEPARATOR . self::LOGFILE;
        $writer = new Zend_Log_Writer_Stream($logfile);
        parent::__construct($writer);
    }
}

?>