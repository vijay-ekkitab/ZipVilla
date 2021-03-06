<?php
include_once('ZipVilla/AuthAdapter.php');

class LoginController extends Zend_Controller_Action
{

    protected function _process($values)
    {
        if (isset($values['username']) && isset($values['password'])) {
            $adapter = new ZipVilla_AuthAdapter($values['username'], $values['password']);
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            if ($result->isValid()) {
                $user = $result->getIdentity();
                $auth->getStorage()->write($user);
                return $user;
            }
        }
        return false;
    }
    
    protected function _fbprocess($values)
    {
        if ($values != null) {
            $adapter = new ZipVilla_AuthAdapter('', '', $values);
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            if ($result->isValid()) {
                $user = $result->getIdentity();
                $auth->getStorage()->write($user);
                return $user;
            }
        }
        return false;
    }

    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('ajaxlogin', 'json')
                    ->addActionContext('fblogin', 'json')
                    ->initContext();
    }

    public function preDispatch()
    {
    	/*
        if (Zend_Auth::getInstance()->hasIdentity()) {
            // If the user is logged in, we don't want to show the login form;
            // however, the logout action should still be available
            if ('logout' != $this->getRequest()->getActionName()) {
                $this->_helper->redirector('index', 'index');
            }
        } else {
            // If they aren't, they can't logout, so that action should
            // redirect to the login form
            if ('logout' == $this->getRequest()->getActionName()) {
                $this->_helper->redirector('index');
            }
        }
        */
        
    }
    
    public function ajaxloginAction() 
    {
        $values = $this->getRequest()->getPost();
        $username = $this->_process($values);
        if (!$username) 
            $username = '';
        else 
            $username = str_replace(AUTH_FIELD_SEPARATOR, ',', $username);
        echo $username;
    }
    
    public function fbloginAction()
    {
        $values = $this->getRequest()->getPost();
        if (!isset($values['email']))
            return;
        $this->_fbprocess($values);
    }

    public function indexAction()
    {
        //check if already logged in via FB
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->_helper->lastDecline();
            return;
        }
        $form = new Application_Form_Login();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
            	if ($this->_process($form->getValues())) {
                    // We're authenticated! Redirect to the last declined or home page.
                    $this->_helper->lastDecline();
                    return;
                    //$this->_helper->redirector('index', 'index');
                }
            	else {
            		$form->setDescription('Username or password is not correct');
            	}
            }
            else {
                $errors = $form->getMessages();
                $errorMsg = '';
                foreach($errors as $field => $error) {
                    foreach($error as $v) {
                        $errorMsg .= str_replace('Value', $field, $v); 
                    }
                    break;// show only first error.
                }
                if ($errorMsg != '') 
                    $form->setDescription($errorMsg);
                $this->view->userdata = $request->getPost();
            }
        }
        $this->view->form = $form;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index'); // back to home page
    }
    
    public function registerAction()
    {
        $form = new Application_Form_Register();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                    
            	   $values = $form->getValues();
            	   unset($values['cnfrm_password']);
            	   unset($values['accept_terms']);
            	   $user = new Application_Model_Users($values);
                   $user->save();
            	   $this->_helper->redirector('index', 'index');
            }
            $this->view->userdata = $request->getPost();
        }
        $this->view->form = $form;
    }

}



