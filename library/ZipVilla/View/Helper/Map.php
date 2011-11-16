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
            $script .= '[' .
                        '\''.$this->view->escape($villa['address__street_name']) . '\',' .
                        '\''.$this->view->escape($villa['address__coordinates__latitude']) . '\',' .
                        '\''.$this->view->escape($villa['address__coordinates__longitude']) . '\',' .
                        '\''.$this->view->escape($villa['address__city']) . '\''. 
                       '],';
            if (($pos == null) && ($villa['address__coordinates__latitude'] > 0)) {
                $pos = 'var zv_map_center_latitude = \''.$this->view->escape($villa['address__coordinates__latitude']).'\'; ' .
                       'var zv_map_center_longitude = \''.$this->view->escape($villa['address__coordinates__longitude']).'\';';
            }
        }
        $script .= '];';
        $invoke = '$(document).ready(function(){mapMarker(\''. $zvMapInfo. '\')});';
        return $script . ' '. $pos . ' '.$invoke;
    }
    
    public function setMapFromDbListings($villas, $zvMapInfo) {
        $script = "";
        $pos = null;
        
        if (($villas == null) || (!is_array($villas))) {
            return "";
        }
        $script .= 'var zv_villa_locations = [';
        foreach($villas as $villa) {
            $script .= '[' .
                        '\''.$this->view->escape($villa->address['street_name']) . '\',' .
                        '\''.$this->view->escape($villa->address['coordinates']['latitude']) . '\',' .
                        '\''.$this->view->escape($villa->address['coordinates']['longitude']) . '\',' .
                        '\''.$this->view->escape($villa->address['city']) . '\''. 
                       '],';
            if (($pos == null) && ($villa->address['coordinates']['latitude'] > 0)) {
                $pos = 'var zv_map_center_latitude = \''.$this->view->escape($villa->address['coordinates']['latitude']).'\'; ' .
                       'var zv_map_center_longitude = \''.$this->view->escape($villa->address['coordinates']['longitude']).'\';';
            }
        }
        $script .= '];';
        $invoke = '$(document).ready(function(){mapMarker(\''. $zvMapInfo. '\')});';
        return $script . ' '. $pos . ' '.$invoke;
    }
    
    
}
?>