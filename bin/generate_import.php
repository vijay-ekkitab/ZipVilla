<?php

function convert_date($date) {
    $parts = split('/', $date);
    return sprintf('%d-%d-%d', $parts[2], $parts[1], $parts[0]);
}


$heads = array( 'Owner Name', 'Street Number', 'Street Name', 'Location', 'City', 'State',
                'PinCode',  'Bedrooms', 'Baths', 'Maximum No. of Guests', 'Title', 'Description',
                'Amenities', '', 'Specifc Conditions', 'Daily', 'Weekly', 'Monthly', 'Date From',
                'Date To', 'Daily', 'Weekly', 'Monthly', 'FileName', "Television", "Cable or Satellite TV","Internet",
                "Wifi", "Air Conditioning", "Hot Water", "Swimming Pool", "Kitchen", "Cook",
                "Parking", "Washer Dryer", "Gym", "Laundry", "Cleaning", "Concierge", "Handicap Access", "Pets Allowed");

$handling = array('ignore', 'address__street_number', 'address__street_name', 'address__location', 'address__city', 'address__state',
                  'address__zipcode', 'bedrooms', 'baths', 'guests', 'title', 'description', 'ignore', 'ignore', 'house_rules',
                  'rate__daily', 'rate__weekly', 'rate__monthly', 'special_rate__from', 'special_rate__to', 'special_rate__daily',
                  'special_rate__weekly', 'special_rate__monthly', 'images', 'amenities:television',
                  'amenities:cable or satellite tv', 'amenities:internet', 'amenities:wifi', 'amenities:air conditioning', 'amenities:hot water',
                  'amenities:swimming pool', 'amenities:kitchen', 'onsite_services:cook', 'amenities:parking', 'amenities:washer dryer', 'amenities:gym',
                  'onsite_services:laundry', 'onsite_services:cleaning', 'onsite_services:concierge', 'suitability:handicap access', 'suitability:pets allowed'); 

$target_heads = array('type', 'address(street_number,street_name,location,city,state,country,zipcode,latitude,longitude)',
                      'bedrooms', 'baths', 'guests', 'onsite_services', 'amenities', 'suitability', 'house_rules', 'rate(daily,weekly,monthly)',
                      '@special_rate(#from,#to,daily,weekly,monthly)', '@booked(#from,#to)', 'title', 'description', 'images', 'rating', 'reviews');


$options = getopt('i:o:');

if ((!isset($options['i'])) || (!isset($options['o']))) {
    echo "Either input or output files not specified.\n";
    echo "Usage: " . $argv[0] . "  -i <input file> -o <output file> \n";
    exit(1);
}

$filename = $options['i'];
$output_filename = $options['o'];

$fh = fopen($filename, 'r');

if ($fh == null) {
    echo "Could not open file!\n";
    exit(1);
}

$header = fgetcsv($fh,0,',','"'); //throw away first line.
$header = fgetcsv($fh,0,',','"'); 

$listings = array();

while(($buffer = fgetcsv($fh,0,',','"')) !== FALSE) {
       $obj = array();
       for($i=0; $i<count($handling); $i++) {
            if ($handling[$i] == 'ignore') {
                continue;
            }
            $buffer[$i] = trim($buffer[$i]);
            if ($buffer[$i] == '') {
                continue;
            }
            $attributes = explode('__', $handling[$i]);
            if (count($attributes) == 1) {
                $attributes = explode(':', $handling[$i]);
                if (count($attributes) == 1) {
                    $obj[$attributes[0]] = $buffer[$i]; 
                }
                else {
                    if (!isset($obj[$attributes[0]])) {
                        $obj[$attributes[0]] = array();
                    } 
                    if (!strcasecmp($buffer[$i], 'y')) {
                        $obj[$attributes[0]][] = $attributes[1];
                    }
                }
            }
            else {
               if (!isset($obj[$attributes[0]])) {
                    $obj[$attributes[0]] = array();
               } 
               $obj[$attributes[0]][$attributes[1]] = $buffer[$i];
            }
       }
       $obj['type'] = 'home';
       $obj['rating'] = rand(1,10); 
       $obj['reviews'] = rand(0,50); 
       $listings[] = $obj;
}

fclose($fh);

$fh = fopen($output_filename, 'w');

$header = '';
foreach($target_heads as $field) {
   $field = preg_replace('/#/', '', $field);
   $header = $header . ",\"$field\""; 
}

$header = preg_replace('/^,/', '', $header);
fprintf($fh, "%s\n", $header);

foreach($listings as $listing) {
    $tmp = '';
    for($i=0; $i<count($target_heads); $i++) {
       if(preg_match('/(.*)\((.+)\)/', $target_heads[$i], $matches)) {
            $matches[1] = preg_replace('/^@/', '', $matches[1]);
            if (isset($listing[$matches[1]])) {
                $tmp = $tmp . '"';
                $subs = split(',', $matches[2]);
                for ($j=0; $j<count($subs); $j++) {
                    if (preg_match('/^#.*/', $subs[$j])) {
                        $subs[$j] = preg_replace('/#/', '', $subs[$j]);
                        if (isset($listing[$matches[1]][$subs[$j]])) {
                            $listing[$matches[1]][$subs[$j]] = convert_date($listing[$matches[1]][$subs[$j]]);
                        }
                    }
                    if (isset($listing[$matches[1]][$subs[$j]])) {
                        $tmp = $tmp . $listing[$matches[1]][$subs[$j]] ;
                    }
                    if ($j < (count($subs) - 1)) {
                        $tmp = $tmp . ','; 
                    }
                }
                $tmp = $tmp . '"';
            }
       }
       else {
            if (isset($listing[$target_heads[$i]])) {
                if (is_array($listing[$target_heads[$i]])) {
                    $tmp = $tmp . '"';
                    foreach($listing[$target_heads[$i]] as $item) {
                        $tmp = $tmp . $item . ','; 
                    }
                    $tmp = preg_replace('/,$/', '', $tmp);
                    $tmp = $tmp . '"';
                }
                else {
                    $tmp = $tmp . '"'.$listing[$target_heads[$i]].'"';
                }
            }
       }
       if ($i < (count($target_heads) - 1)) {
            $tmp = $tmp . ',';
       }
    }
    fprintf($fh, "%s\n", $tmp);
}
fclose($fh);

?>
