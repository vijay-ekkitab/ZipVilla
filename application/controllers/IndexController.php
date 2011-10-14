<?php
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    
	public function preDispatch()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            // Only logged in users have access to the Home Page;
            // Direct all other users to the Login Page.
            $this->_helper->redirector('index', 'login');
        } 
    }

    public function indexAction()
    {
        $this->view->listings = $this->_helper->listingsManager->query();
    }

    public function addAction()
    {
    	
        $form = new Application_Form_Listing();
        $form->submit->setLabel('Add');
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $type = $form->getValue('type');
                $vals = array();
                $vals['state'] = $form->getValue('state');
                $this->_helper->listingsManager->insert($type, $vals);
                $this->_helper->redirector('index');
            } else {
                $form->populate($formData);
            }
        }
    }

    public function editAction()
    {
        $form = new Application_Form_Listing();
        $form->submit->setLabel('Save');
        $this->view->form = $form;
        if ($this->getRequest()->isPost()) {
                $formData = $this->getRequest()->getPost();
                if ($form->isValid($formData)) {
                        $id = $form->getValue('id');
                        $vals = array();
                        $vals['type'] = $form->getValue('type');
                        $vals['state'] = $form->getValue('state');
                        $this->_helper->listingsManager->update($id, $vals);
                        $this->_helper->redirector('index');
                } else {
                        $form->populate($formData);
                }
        } else {
                $id = $this->_getParam('id', 0);
                if ($id > 0) {
                    $listing = $this->_helper->listingsManager->queryById($id);
                    $vals = array();
                    $vals['type'] = $listing->type;
                    $vals['state'] = $listing->address['state'];
                    $vals['id'] = $id;
                    $form->populate($vals);
                }
        }
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $del = $this->getRequest()->getPost('del');
            if ($del == 'Yes') {
                $id = $this->getRequest()->getPost('id');
                $this->_helper->listingsManager->delete($id);
            }
            $this->_helper->redirector('index');
        } else {
            $id = $this->_getParam('id', 0);
            $this->view->listing = $this->_helper->listingsManager->queryById($id);
        }
    }


}







