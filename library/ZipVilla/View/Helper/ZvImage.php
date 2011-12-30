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
        $city = '';
        $image = '';
        if ($listing == null) {
            return null;
        }
        if ($listing instanceof Application_Model_Listings) {
            $city = $listing->address['city'];
            $image = $listing->images[0];
        }
        else {
            $city = isset($listing['address__city']) ? $listing['address__city'] : '';
            $image = isset($listing['images'][0]) ? $listing['images'][0] : '';
        }
        if (($city != '') && ($image != '')) {
            $city = strtolower(str_replace(' ', '', $city));
            return $baseUrl.'/images/listings/'.$city.'/'.$image;
        }
            
        return $baseUrl.'/images/listings/default_img.jpg';
    }
    
    public function getImagePathPrefix($baseUrl, $listing) {
        if ($listing instanceof Application_Model_Listings) {
            $city = $listing->address['city'];
        }
        else {
            $city = $listing['address__city'];
        }
        $city = strtolower(str_replace(' ', '', $city));
        return $baseUrl.'/images/listings/'.$city;
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
