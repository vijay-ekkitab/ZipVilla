<?php
class ShowController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    
	public function preDispatch()
    {
    }

    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        $request = $this->getRequest();
        $sourcefile = null;
        if ($request->isGet()) {
            $doc = $request->getParam('doc');
            if ($doc != null) 
                $sourcefile = $doc.'.txt'; 
        }
        $this->view->sourcefile = $sourcefile;
    }


}







