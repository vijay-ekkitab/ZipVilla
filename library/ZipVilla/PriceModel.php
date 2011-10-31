<?php

class PriceModel {

    protected $special_rates = null;
    protected $standard_rate = null;

    public function __construct($special_rates, $standard_rate) {
        $this->special_rates = $special_rates; //an array of rate slabs, each for a date range.
        $this->standard_rate = $standard_rate; //a rate slab without dates.
    }

    private function get_interval($start, $end) {
        if (($start == null) || ($end == null)) {
            return(0);
        }
        if ($start->sec >= $end->sec) {
            return(0);
        }
        return (($end->sec - $start->sec) / 86400);
    }
    
    private static function sort_on_time($a, $b) {
    	if ($a['period']['from']->sec < $b['period']['from']->sec) {
        	return -1;
    	}
    	elseif ($a['period']['from']->sec > $b['period']['from']->sec) {
        	return 1;
    	}
    	return 0;
	}

	private function print_array($a) {
    	foreach($a as $z) {
        	echo "   From: ".date('d-M-Y',$z['period']['from']->sec)."  To: ".date('d-M-Y',$z['period']['to']->sec)."\n";
    	}
	}

	private function filter_specials($specials, $start, $end) {
    	$result = array();
    	foreach($specials as $special) {
        	if (!(($special['period']['from']->sec >= $end->sec) ||
            	  ($special['period']['to']->sec <= $start->sec))) {
           		$result[] = $special;
        	}
    	}
    	usort($result, 'static::sort_on_time');
    	return $result;
	}
	
	private function add_rate_slab($start, $end, $rate) {
    	$result = array();
    	$result['from'] = $start;
    	$result['to'] = $end;
    	$result['days'] = $this->get_interval($start, $end);
    	$result['rate'] = $rate;
    	return $result;
	}
	

    public function get_rate_structure($start, $end) {

        $results = array();

        if (($this->special_rates == null) ||
            ($start == null) ||
            ($end == null)) {
            if ($this->standard_rate == null) {
                return($results);
            }
            $results[] = $this->add_rate_slab($start, $end, $this->standard_rate);
            return($results);
        }

        if ($start->sec >= $end->sec) {
            return($results);
        }
        $specials = $this->filter_specials($this->special_rates, $start, $end);
        
	    foreach($specials as $special) {
	    	
	    	$from = $special['period']['from'];
	    	$to   = $special['period']['to'];
	    	
        	if ($from->sec > $start->sec) {
             	$results[] = $this->add_rate_slab($start, $from, $this->standard_rate);
             	$start = $from;
        	}
        	if ($to->sec < $end->sec) {
             	$results[] = $this->add_rate_slab($start, $to, $special['rate']);
             	$start = $to;
        	}
        	elseif ($to->sec >= $end->sec) {
             	$results[] = $this->add_rate_slab($start, $end, $special['rate']);
             	$start = $end;
        	}
        	if ($start->sec >= $end->sec) {
           		break;
        	}
    	}

    	if ($start->sec < $end->sec) {
        	$results[] = $this->add_rate_slab($start, $end, $this->standard_rate);
    	}

    	return($results);
        
    }

    public function get_average_rate($start, $end, $quiet_mode = TRUE) {

        $slabs = $this->get_rate_structure($start, $end);
        $total_price = 0;
        $total_days = 0;
        foreach($slabs as $slab) {
        	   if (!$quiet_mode) {
               		echo "From: ".
                    	($slab['from'] == null ? '<null>' : date('d-M-Y',$slab['from']->sec)).
                    	"  To: ".
                    	($slab['to'] == null ? '<null>' : date('d-M-Y',$slab['to']->sec)).
                    	"  [days ".
                    	$slab['days'].
                    	"]  Rate: ".
                    	$slab['rate']['daily'].",".$slab['rate']['weekly'].",".$slab['rate']['monthly']."\n";
        	   }
               $rate = $slab['rate'];
               $days = $slab['days'];
               if ($days > 0) {
                    $total_days += $days;
                    $months = intval($days/30); 
                    $days = $days%30;
                    $weeks = intval($days/7);
                    $days = $days%7;
                    $total_price += round($months*$rate['monthly'] + $weeks*$rate['weekly'] + $days*$rate['daily']);
               }
               else {
                   $total_price = $rate['daily'];
                   $total_days  = 1;
                   break;
               }
        }
        return $total_days > 0 ? round($total_price/$total_days) : 0;
    }

}


?>
