<?php

class PriceModel {

    protected $special_rates = null;
    protected $standard_rate = null;

    public function __construct($special_rates, $standard_rate) {
        $this->special_rates = $special_rates; //an array of rate slabs, each for a date range.
        $this->standard_rate = $standard_rate; //string
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

    public function get_rate_structure($start, $end) {

        $results = array();


        if (($this->special_rates == null) ||
            ($start == null) ||
            ($end == null)) {
            if ($this->standard_rate == null) {
                return($results);
            }
            $result = array();
            $result['from'] = $start;
            $result['to'] = $end;
            $days = $this->get_interval($start, $end);
            $result['days'] = $days;
            $result['rate'] = $this->standard_rate;
            $results[] = $result;
            return($results);
        }

        if ($start->sec >= $end->sec) {
            return($results);
        }

        $ranges = array();
        $range = array();
        $range['start'] = $start;
        $range['end'] = $end;
        $ranges[] = $range;

        while (count($ranges) > 0) {

            $nextranges = array();
   
            foreach ($ranges as $range) {

                $start = $range['start'];
                $end = $range['end'];
                $processed = FALSE;

                foreach($this->special_rates as $special) {
           
                    if ($start->sec < $special['from']->sec) {
                        if ($end->sec > $special['from']->sec) {
                            if ($end->sec > $special['to']->sec) {
                                $days = $this->get_interval($special['from'], $special['to']);
                                $result = array();
                                $result['from'] = $special['from'];
                                $result['to'] = $special['to'];
                                $result['days'] = $days;
                                $result['rate'] = $special['rate'];
                                $results[] = $result;
                                $range = array();
                                $range['start'] = $start;
                                $range['end'] = $special['from'];
                                $nextranges[] = $range;
                                $range = array();
                                $range['start'] = $special['to'];
                                $range['end'] = $end;
                                $nextranges[] = $range;
                                $processed = TRUE;
                                break;
                            }
                            else {
                                $days = $this->get_interval($special['from'], $end);
                                $result = array();
                                $result['from'] = $special['from'];
                                $result['to'] = $end;
                                $result['days'] = $days;
                                $result['rate'] = $special['rate'];
                                $results[] = $result;
                                $range = array();
                                $range['start'] = $start;
                                $range['end'] = $special['from'];
                                $nextranges[] = $range;
                                $processed = TRUE;
                                break;
                            }
                        }
                    }
                    else {
                        if ($start->sec < $special['to']->sec) {
                            if ($end->sec > $special['to']->sec) {
                                $days = $this->get_interval($start, $special['to']);
                                $result = array();
                                $result['from'] = $start;
                                $result['to'] = $special['to'];
                                $result['days'] = $days;
                                $result['rate'] = $special['rate'];
                                $results[] = $result;
                                $range = array();
                                $range['start'] = $special['to'];
                                $range['end'] = $end;
                                $nextranges[] = $range;
                                $processed = TRUE;
                                break;
                            }
                            else {
                                $days = $this->get_interval($start, $end);
                                $result = array();
                                $result['from'] = $start;
                                $result['to'] = $end;
                                $result['days'] = $days;
                                $result['rate'] = $special['rate'];
                                $results[] = $result;
                                $processed = TRUE;
                                break;
                            }
                        }
                    }
   
                } 
                if (!$processed) {
                    $days = $this->get_interval($start, $end);
                    $result = array();
                    $result['from'] = $start;
                    $result['to'] = $end;
                    $result['days'] = $days;
                    $result['rate'] = $this->standard_rate;
                    $results[] = $result;
                }
            }
            $ranges = $nextranges;
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
                    	$slab['rate']."\n";
        	   }
               $rates = explode(',',$slab['rate']);
               $days = $slab['days'];
               if ($days > 0) {
                    $total_days += $days;
                    $months = intval($days/30); 
                    $days = $days%30;
                    $weeks = intval($days/7);
                    $days = $days%7;
                    $total_price += round($months*$rates[2] + $weeks*$rates[1] + $days*$rates[0]);
               }
               else {
                   $total_price = $rates[0];
                   $total_days  = 1;
               }
        }
        return $total_days > 0 ? round($total_price/$total_days) : 0;
    }

}


?>
