<?php
include_once 'ZipVilla/TypeConstants.php';

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
    
    public function getImagePathPrefix($baseUrl, $listing) {
        if ($listing instanceof Application_Model_Listings) {
            return $baseUrl.'/images/listings/'.strtolower($listing->address['city']);
        }
        return $baseUrl.'/images/listings/'.strtolower($listing['address__city']);
    }
    
    public function createThumb($src, $dest) {
    
      $info = pathinfo($src);
      if (strtolower($info['extension']) != 'jpg')
         return;
         
      $img      = imagecreatefromjpeg($src);
      $width    = imagesx($img);
      $height   = imagesy($img);
      $new_width = THUMB_WIDTH;
      $new_height = floor( $height * ( THUMB_WIDTH / $width ) );
      $tmp_img = imagecreatetruecolor($new_width, $new_height);
      imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
      imagejpeg($tmp_img,$dest);
      
    }
        
}
?>
