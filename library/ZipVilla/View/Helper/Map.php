<?php
class ZipVilla_View_Helper_Map
{

    public $view;
    
    public function map() {
        return $this;
    }
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
    
    public function setMapFromSearchResults($villas, $zvMapInfo) {
        $script = "";
        $pos = null;
        
        if (($villas == null) || (!is_array($villas))) {
            return "";
        }
        $script .= 'var zv_villa_locations = [';
        foreach($villas as $villa) {
        	if( ($villa['address__coordinates__latitude'] > 0) && 
					    ($villa['address__coordinates__longitude'] > 0)) {
					  $script .= '[' .
                        '\''.$this->view->escape(preg_replace("/\'/","\\\'",$villa['title'])) . '\',' .
                        '\''.$this->view->escape($villa['address__coordinates__latitude']) . '\',' .
                        '\''.$this->view->escape($villa['address__coordinates__longitude']) . '\',' .

                        '\'/list?id=' .
					  						    $this->view->escape($villa['id']) . '\'' .
                        // '\''.$this->view->escape(preg_replace("/\'/","\\\'",$villa['title'])) . '\',' .
                        // '\''.$this->view->escape($villa['address__street_name']) . '\',' .
                        // '\''.$this->view->escape($villa['address__city']) . '\''.
                       '],';
            if ($pos == null) {
                $pos = 'var zv_map_center_latitude = \''.$this->view->escape($villa['address__coordinates__latitude']).'\'; ' .
                       'var zv_map_center_longitude = \''.$this->view->escape($villa['address__coordinates__longitude']).'\';';
            }
        	}
        }
        $script .= '];';
        //$invoke = '$(document).ready(function(){mapMarker(\''. $zvMapInfo. '\')});';
        //return $script . ' '. $pos . ' '.$invoke;
        return $script . ' '. $pos;
    }
    
    public function setMapFromDbListings($villas, $zvMapInfo) {
        $script = "";
        $pos = null;

        if (($villas == null) || (!is_array($villas))) {
            return "";
        }
        $script .= 'var zv_villa_locations = [';
        foreach($villas as $villa) {
            if (isset($villa->address['coordinates']['latitude']) &&
                isset($villa->address['coordinates']['latitude'])) {
            	if( ($villa->address['coordinates']['latitude'] > 0) && 
    						  ($villa->address['coordinates']['longitude'] > 0)) {
                    $script .= '[' .
                                '\''.$this->view->escape($villa->title) . '\',' .
                                '\''.$this->view->escape($villa->address['coordinates']['latitude']) . '\',' .
                                '\''.$this->view->escape($villa->address['coordinates']['longitude']) . '\',' .
                    
                                '\'/list?id=' .
                                    $this->view->escape($villa->id) . '\'' .
                    
                                // '\''.$this->view->escape($villa->title) . ',' 
                                //    .$this->view->escape($villa->address['street_name']) . ',' 
                                //    .$this->view->escape($villa->address['city']) . '.\''. 
                               '],';
                    if( $pos == null ) {
                        $pos = 'var zv_map_center_latitude = \''.$this->view->escape($villa->address['coordinates']['latitude']).'\'; ' .
                               'var zv_map_center_longitude = \''.$this->view->escape($villa->address['coordinates']['longitude']).'\';';
                    }
            	}
            }
        }
        $script .= '];';
        // $invoke = '$(document).ready(function(){mapMarker(\''. $zvMapInfo. '\')});';
        //return $script . ' '. $pos . ' '.$invoke;
        return $script . ' '. $pos;
    }
    
    
}
?>
