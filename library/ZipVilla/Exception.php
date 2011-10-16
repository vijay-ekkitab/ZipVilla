<?php

/**
 * @see Zend_Exception
 */
include_once 'Zend/Exception.php';

class ZipVilla_Exception extends Zend_Exception {}

/**
  * Throw an exception
  *
  * @param  string $msg  Message for the exception
  * @throws ZipVilla_Exception
  */
function throwZVException($msg, Exception $e = null)
{
   throw new ZipVilla_Exception($msg, 0, $e);
}

