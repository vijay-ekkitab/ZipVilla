<?php

class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract
{

    protected $_name = 'users';
    protected $_salt = '12ab34cd56ef78gh90ij';

    public function getUser($id)
    {
        $id = (int)$id;
        $row = $this->fetchRow('id = ' . $id);
        if (!$row) {
            throw new Exception("Could not find row $id");
        }
        return $row->toArray();
    }

    public function addUser($values)
    {
    	if((!isset($values['username'])) ||
    	   (!isset($values['firstname'])) ||
    	   (!isset($values['lastname'])) ||
    	   (!isset($values['password']))) {
    	   	return FALSE;
    	}
        $data = array(
            'username' => $values['username'],
        	'password' => sha1($values['password'] . $this->_salt),
            'firstname' => $values['firstname'],
        	'lastname' => $values['lastname'],
        	'email' => $values['username'],
        	'salt'  => $this->_salt,
        	'role'  => 'user',
        	'date_modified'  => Zend_Date::now()
        );
        $this->insert($data);
        return TRUE;
    }

    public function updateUser($id, $username, $password, $firstname, $lastname)
    {
        $data = array(
            'username' => $username,
        	'password' => sha1($password . $_salt),
            'firstname' => $firstname,
        	'lastname' => $lastname,
        	'email' => $username,
        	'salt'  => $_salt,
        	'role'  => 'user',
        	'date_modified'  => Zend_Date::now()
        );
        $this->update($data, 'id = '. (int)$id);
    }

    public function deleteUser($id)
    {
        $this->delete('id =' . (int)$id);
    }
    

}

