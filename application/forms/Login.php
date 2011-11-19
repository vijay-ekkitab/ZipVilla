<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setName("login");
        $this->setMethod('post');
        
        $this->addElement('text', 
        				  'username', 
        				  array(
            					'filters' => array('StringTrim', 'StringToLower'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
            									),
            					'required'   => true,
            					'label'      => 'Username:',
        				 ));

        $this->addElement('password', 
        				  'password', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
            									),
            					'required'   => true,
            					'label'      => 'Password:',
        				  ));

    }


}

