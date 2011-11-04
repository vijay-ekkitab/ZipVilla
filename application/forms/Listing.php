<?php

class Application_Form_Listing extends Zend_Form
{
	private $lm = null;
	
	public function setListingsManager($lm) {
		$this->lm = $lm;
	}

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
               
        $street = new Zend_Form_Element_Text('street_name');
        $street->setLabel('Street')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty');
        
        $city = new Zend_Form_Element_Text('city');
        $city->setLabel('City')
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
        
        $rooms = new Zend_Form_Element_Text('bedrooms');
        $rooms->setLabel('Bedrooms')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('Int')
              ->addValidator('NotEmpty');
              
        $guests = new Zend_Form_Element_Text('guests');
        $guests->setLabel('Guests')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('Int')
              ->addValidator('NotEmpty');
        
        $lat = new Zend_Form_Element_Text('latitude');
        $lat->setLabel('Latitude')
        ->setRequired(false)
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
        //->addValidator('NotEmpty');

        $lng = new Zend_Form_Element_Text('longitude');
        $lng->setLabel('Longitude')
        ->setRequired(false)
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
        //->addValidator('NotEmpty');
        
        /*
        $et = new Zend_Form_Element_MultiCheckbox('entertainment_options');
        $et->setLabel('Entertainment Options')
              ->setRequired(true);
        if ($this->lm != null) {
        	$options = $this->lm->getEnumOptions("entertainment_options");
        	if ($options != null) {
        		foreach ($options as $option) {
        			$et->addMultiOption($option, $option);
        		}		
        	}
        }
        */
        
              
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');
        $this->addElements(array($id, $type, $street, $city, $state, $rooms, $guests, $lat, $lng, /*$et,*/ $submit));
    }    

}

