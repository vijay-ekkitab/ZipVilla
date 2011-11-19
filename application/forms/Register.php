<?php
include_once("ZipVilla/TypeConstants.php");
class Application_Form_Register extends Zend_Form
{

    public function init()
    {
        $this->setName("register");
        $this->setMethod('post');
        
        $this->addElementPrefixPath(
                'ZipVilla',
                APPLICATION_PATH.'/forms/validate/ZipVilla/',
                'validate'
        );
        
        $this->addElement('text', 
        				  'emailid', 
        				  array(
            					'filters' => array('StringTrim', 'StringToLower'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
                									'EmailAddress',
                									array('ValidateRegisteredUser', false, array())),
            					'required'   => true,
            					'label'      => 'Email Address:',
        				 ));
        				 
        $this->addElement('text', 
        				  'firstname', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
            									),
            					'required'   => true,
            					'label'      => 'First Name:',
        				 ));
        				 
        $this->addElement('text', 
        				  'lastname', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
            									),
            					'required'   => true,
            					'label'      => 'Last Name:',
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
        				  
        $this->addElement('password', 
        				  'cnfrm_password', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
                									array('Identical', false, array('token' => 'password'))
            									),
            					'required'   => true,
            					'label'      => 'Confirm Password:',
        				  ));
        				  
        $this->addElement('checkbox', 'accept_terms',
        				   array('required'   => true,
        				         'uncheckedValue'=> '',
                                 'checkedValue' => 'on',
        				         'validators' => array(
        				                            array('notEmpty', true,
        				                                array('messages' => array(
        				                                      'isEmpty' => 'You must agree to the terms.')))))
        				 );

        				  
    }


}

