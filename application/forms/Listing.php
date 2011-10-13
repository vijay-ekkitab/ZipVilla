<?php

class Application_Form_Listing extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        $this->setName('listing');
        $id = new Zend_Form_Element_Hidden('id');
        $id->addFilter('Alnum');
        $type = new Zend_Form_Element_Text('type');
        $type->setLabel('Type')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('NotEmpty');
        $state = new Zend_Form_Element_Text('state');
        $state->setLabel('State')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty');
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');
        $this->addElements(array($id, $type, $state, $submit));
    }


}

