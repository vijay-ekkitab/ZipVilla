<?php
include_once("ZipVilla/TypeConstants.php");
class Application_Form_EditPropertyPage1 extends Zend_Form
{

    public function init()
    {
        $this->setName("edit property page 1");
        $this->setMethod('post');
       
        
        $this->addElement('text', 
        				  'title', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(array('StringLength', false, array(0, 80))),
        				     	'required'   => true,
            					'label'      => 'Title'));
        				 
        $this->addElement('textarea', 
        				  'description', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(
                									array('StringLength', false, array(0, 1024)),
            									),
            					'required'   => true,
            					'label'      => 'Description'
        				 ));
        				 
        $this->addElement('text', 
        				  'price_day', 
        				  array(
            					'filters' => array('StringTrim'),
            					'validators' => array(
                									array('Between', false, array(100, 20000)),
            									),
            					'required'   => true,
            					'label'      => 'Price'
        				 ));
        				 
        $this->addElement('textarea', 
                          'address', 
                          array(
                                'filters' => array('StringTrim'),
                                'validators' => array(
                                                    array('StringLength', false, array(0, 256)),
                                                ),
                                'required'   => true,
                                'label'      => 'Street Address'
                         ));

        $this->addElement('text', 
                          'location', 
                          array(
                                'filters' => array('StringTrim'),
                                'validators' => array(
                                                    array('StringLength', false, array(0, 80)),
                                                ),
                                'required'   => true,
                                'label'      => 'Location'
                         ));
                         
        $this->addElement('text', 
                          'city', 
                          array(
                                'filters' => array('StringTrim'),
                                'validators' => array(
                                                    array('StringLength', false, array(0, 80)),
                                                ),
                                'required'   => true,
                                'label'      => 'City'
                         ));
                         
                         
        $this->addElement('text', 
                          'zipcode', 
                          array(
                                'filters' => array('StringTrim'),
                                'validators' => array(
                                                    array('Int', false),
                                                ),
                                'required'   => true,
                                'label'      => 'Pin Code'
                         ));

        $this->addElement('checkbox', 'terms',
        				   array('required'   => true,
        				         'uncheckedValue'=> '',
                                 'checkedValue' => 'yes',
        				         'validators' => array(
        				                            array('NotEmpty', true,
        				                                array('messages' => array(
        				                                      'isEmpty' => 'You must agree to the terms.')))))
        				 );

        				  
    }


}

