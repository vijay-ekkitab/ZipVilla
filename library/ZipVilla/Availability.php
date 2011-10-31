<?php

class Availability {

    public function __construct() {
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

	private function print_booking_array($a) {
    	foreach($a as $z) {
        	echo "   From: ".date('d-M-Y',$z['from']->sec)."  To: ".date('d-M-Y',$z['to']->sec)."\n";
    	}
	}

	private function filter_bookings($bookings, $start, $end) {
    	$result = array();
    	foreach($bookings as $booking) {
        	if (!(($booking['period']['from']->sec >= $end->sec) ||
            	  ($booking['period']['to']->sec <= $start->sec))) {
           		$result[] = $booking;
        	}
    	}
    	usort($result, 'static::sort_on_time');
    	return $result;
	}
	
	private function add_booking($start, $end) {
    	$result = array();
    	$result['from'] = $start;
    	$result['to'] = $end;
    	$result['days'] = $this->get_interval($start, $end);
    	return $result;
	}
	
	public function get_booked_dates($bookings, $start, $days, $quiet_mode = TRUE) {
		$results = array();
		if (($bookings == null) || ($start == null)) {
			return $results;
		}
		if (($days == null) || ($days == 0)) {
			return $results;
		}
		$end = new MongoDate($start->sec + (86400 * $days));
		$bookings = $this->filter_bookings($bookings, $start, $end);
		
	    foreach($bookings as $booking) {
	    	$from = $booking['period']['from'];
	    	$to   = $booking['period']['to'];
	    	
        	if ($from->sec >= $start->sec) {
             	$start = $from;
        	}
        	if ($to->sec < $end->sec) {
             	$results[] = $this->add_booking($start, $to);
             	$start = $to;
        	}
        	elseif ($to->sec >= $end->sec) {
             	$results[] = $this->add_booking($start, $end);
             	$start = $end;
        	}
        	if ($start->sec >= $end->sec) {
           		break;
        	}
    	}
    	if (!$quiet_mode) {
    		$this->print_booking_array($results);
    	}
    	return($results);
	}

    public function is_available($bookings, $start, $end) {

        if (($bookings == null) || ($start == null) || ($end == null)) {
            return TRUE;
        }
        if ($start->sec >= $end->sec) {
            return FALSE;
        }
	    foreach($bookings as $booking) {
	    	
	    	$from = $booking['period']['from'];
	    	$to   = $booking['period']['to'];
	    	
        	if (($from->sec >= $start->sec) &&
        	    ($from->sec <= $end->sec)) {
        	    	return FALSE;
        	} 
    	}
    	return TRUE;
    }
}


?>
