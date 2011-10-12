<?php

/**
 * Action Helper for loading forms
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class ZipVilla_Helper_ListingManager extends Zend_Controller_Action_Helper_Abstract
{
    private $secret;

    public function __construct()
    {
        $this->secret = "this is a secret message"; 
    }

    public function checkout()
    {
        return $this->secret;
    }

    /**
     * Strategy pattern: call helper as broker method
     * 
     * @param  string $name 
     * @param  array|Zend_Config $options 
     * @return Zend_Form
    public function direct($name, $options = null)
    {
        return $this->loadForm($name, $options);
    }
     */
}

?>

