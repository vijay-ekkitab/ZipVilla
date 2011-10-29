<?php

class Application_Form_Register extends Zend_Form
{

    public function init()
    {
        $this->setName("register");
        $this->setMethod('post');
        
        $this->addElement('text', 
        				  'username', 
        				  array(
            					'filters' => array('StringTrim', 'StringToLower'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
                									'EmailAddress',
                									array('Db_NoRecordExists', false,
                										  array('table' => 'users',
                												'field'	=> 'username'))
            										),
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
        				  'confirm_password', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(
                									array('StringLength', false, array(0, 50)),
                									array('Identical', false, array('token' => 'password'))
            									),
            					'required'   => true,
            					'label'      => 'Confirm Password:',
        				  ));
        				  
        $this->addElement('captcha', 'captcha',
        				   array('label' => 'Please verify you are human',
        				   		 'captcha' => array(
        				   						'captcha' => 'Figlet',
        				   						'wordlen' => 6,
        				   						'timeout' => 300
        				   					  )
        				   		)
        				 );

        $this->addElement('submit', 
        				  'login', 
        				  array(
            					'required' => false,
            					'ignore'   => true,
            					'label'    => 'Login',
        				  ));
        				  
        $this->setDecorators(array(
            						'FormElements',
            						array(
            							'HtmlTag', 
            							array(
            								'tag' => 'dl', 
            								'class' => 'zend_form'
            							)
            						),
            						array(
            							'Description', 
            							array(
            								'placement' => 'prepend'
            							)
            						),
            						'Form'
        					));
    }


}

