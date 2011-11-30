<?php
class ZipVilla_View_Helper_ZvImage
{

    //public $view;
    
    public function zvImage() {
        return $this;
    }
    
    /*public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }*/
    
    public function getFirstListingImage($baseUrl, $listing) {
        $logger = Zend_Registry::get('zvlogger');
        if (($listing != null) && isset($listing['address__city']) && isset($listing['images'][0])) 
            return $baseUrl.'/images/listings/'.strtolower($listing['address__city']).'/'.$listing['images'][0];
        return $baseUrl.'/images/listings/default_img.jpg';
    }
    
}
?>
