<?php

class Application_Form_AddOwner extends Zend_Form
{

    public function init()
    {
        $this->setName('addOwner');
        $this->setMethod('post');
        
        $users = Application_Model_Users::find();
        $uids = array();
        foreach($users as $user) {
            $uid = $user->id;
            $email= $user->emailid;
            $uids[$uid] = $email;
        }
        $id = new Zend_Form_Element_Hidden('id');
        $dropdown = new Zend_Form_Element_Select('dropdown');
        $dropdown->setLabel('Select User:')
                 ->setMultiOptions($uids);
               
        $submit = new Zend_Form_Element_Submit('submit');
        $this->addElements(array($id, $dropdown, $submit));
    }


}

