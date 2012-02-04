<?php

class PriceModel {

    protected $special_rates = null;
    protected $standard_rate = null;

    public function __construct($special_rates, $standard_rate) {
        $this->special_rates = $special_rates; //an array of rate slabs, each for a date range.
        if ($this->special_rates != null) {
            for ($i=0; $i<count($this->special_rates); $i++) {
                $this->special_rates[$i]['period']['from'] = $this->cleanDate($this->special_rates[$i]['period']['from']);
                $this->special_rates[$i]['period']['to'] = $this->cleanDate($this->special_rates[$i]['period']['to']);
            }
        }
        $this->standard_rate = $standard_rate; //a rate slab without dates.
    }
    
    private function cleanDate($date) {
        return new MongoDate(strtotime(date('d-M-Y', $date->sec)));
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
        if (!isset($result['rate']['weekly'])) {
            $result['rate']['weekly'] = 7*$result['rate']['daily'];
        }
        if (!isset($result['rate']['monthly'])) {
            $result['rate']['monthly'] = 30*$result['rate']['daily'];
        }
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
        $result = $this->get_total_rate($start, $end, $quiet_mode);
        $rate = $result['rate'];
        $days = $result['days'];
        return $days > 0 ? round($rate/$days) : 0;
    }

    public function get_total_rate($start, $end, $quiet_mode = TRUE) {

        $logger = Zend_Registry::get('zvlogger');
        $slabs = $this->get_rate_structure($start, $end);
        $total_price = 0;
        $total_days = 0;
        foreach($slabs as $slab) {
        	   if (!$quiet_mode) {
               		$logger->debug("From: ".
                    	($slab['from'] == null ? '<null>' : date('d-M-Y',$slab['from']->sec)).
                    	"  To: ".
                    	($slab['to'] == null ? '<null>' : date('d-M-Y',$slab['to']->sec)).
                    	"  [days ".
                    	$slab['days'].
                    	"]  Rate: ".
                    	$slab['rate']['daily'].",".$slab['rate']['weekly'].",".$slab['rate']['monthly']."\n");
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
        return array('rate' => $total_price, 'days' => $total_days);
    }
    
    public static function addSpecialRate($specials, $from, $to, $rate, $addrate)
    {
        $fromsec   = $from->sec;       
        $tosec     = $to->sec;
        if ($fromsec > $tosec)
            return $specials;
        $newspecials = array();
        
        foreach ($specials as $special) {
            $spfromsec = $special['period']['from']->sec;       
            $sptosec   = $special['period']['to']->sec;
            $changes = array();
            $sprate = $special['rate'];
            $fragment = true;
            if ($sprate['daily'] == $rate['daily']) {
                $fragment = false;
            }
            
            if ($fromsec > $spfromsec) {
                if ($fromsec <= $sptosec) {
                    $prevday = $fromsec-86400;
                    if ($fragment) {
                        $changes[] = array('from'=> $spfromsec, 'to' => $prevday);
                    }
                    else {
                        $fromsec = $spfromsec;
                    }
                    if ($tosec < $sptosec) {
                        $nextday = $tosec+86400;
                        if ($fragment) {
                            $changes[] = array('from'=> $nextday, 'to' => $sptosec);
                        }
                        else {
                            $tosec = $sptosec;
                        }
                    }
                }
                else {
                    $changes[] = array('from'=> $spfromsec, 'to' => $sptosec);
                }
            }
            else {
                if ($tosec >= $spfromsec) {
                    if ($tosec < $sptosec) {
                        $nextday = $tosec+86400;
                        if ($fragment) {
                            $changes[] = array('from'=> $nextday, 'to' => $sptosec);
                        }
                        else {
                            $tosec = $sptosec;
                        }
                    }
                }
                else {
                    $changes[] = array('from'=> $spfromsec, 'to' => $sptosec);
                }
            }
            foreach ($changes as $change) {
                $newspecial = array('rate' => $special['rate']);
                $period = array('from' => new MongoDate($change['from']),
                                'to'   => new MongoDate($change['to']));
                $newspecial['period'] = $period;
                $newspecials[] = $newspecial;
            }
        }
        if ($addrate) {
            $newspecial = array('rate' => $rate);
            $period = array('from' => new MongoDate($fromsec),
                            'to'   => new MongoDate($tosec));
            $newspecial['period'] = $period;
            $newspecials[] = $newspecial;
        }
        return($newspecials);
    }

}


?>
