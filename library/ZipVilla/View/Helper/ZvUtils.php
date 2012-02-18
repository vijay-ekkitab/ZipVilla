<?php
include_once 'ZipVilla/TypeConstants.php';

class ZipVilla_View_Helper_ZvUtils
{

    public function zvUtils() {
        return $this;
    }
    
    public function calcPages($max, $pgsize, $start) {

        $pages = array();
        $prevpage = FALSE;
        $nextpage = FALSE;
        $thispage = 1;
        if ($max > 0) {
            $numpages = ceil($max / $pgsize);
            $thispage = ceil(($start + 1) / $pgsize);
            $firstpage = 1;
            $lastpage = $numpages;
            if ($lastpage > $firstpage) {
                $backpages = $thispage - $firstpage;
                $pagesahead = $lastpage - $thispage;
                $prevpage = FALSE;
                $nextpage = FALSE;
                if (($backpages > 0) && ($pagesahead > 0)){
                    $prevpage = $thispage-1;
                    $nextpage = $thispage+1;
                    $pages[0] = $thispage -1;
                    $pages[1] = $thispage;
                    $pages[2] = $thispage +1;
                }
                elseif ($backpages > 0) {
                    $prevpage = $thispage-1;
                    if ($backpages >= 2) {
                        $pages[0] = $thispage -2;
                        $pages[1] = $thispage -1;
                        $pages[2] = $thispage;
                    }
                    else {
                        $pages[0] = $thispage -1;
                        $pages[1] = $thispage;
                    }
                }
                elseif ($pagesahead > 0) {
                    $nextpage = $thispage+1;
                    if ($pagesahead >= 2) {
                        $pages[0] = $thispage;
                        $pages[1] = $thispage +1;
                        $pages[2] = $thispage +2;
                    }
                    else {
                        $pages[0] = $thispage;
                        $pages[1] = $thispage +1;
                    }
                }
            }
        }
        return array('pages'=>$pages, 'prev'=> $prevpage, 'next'=>$nextpage, 'this'=>$thispage);
    }
    
    public function diffListings($l1, $l2)
    {
       $reviewAttributes  = array('owner' => 'owner',
                                  'address' => 'address',
                                  'bedrooms' => 'bedrooms',
                                  'baths' =>  'baths',
                                  'guests' => 'capacity',
                                  'onsite_services' => 'amenities',
                                  'amenities' => 'amenities',
                                  'activities' => 'amenities',
                                  'neighbourhood' => 'neighbourhood',
                                  'suitability' => 'amenities',
                                  'house_rules' => 'house rules',
                                  'rate' => 'base rate',
                                  'title' => 'title',
                                  'description' => 'description',
                                  'images' => 'images',
                                  'shared' => 'rental model');
        $d1 = $l1->getDoc();
        $d2 = $l2->getDoc();
        $diffs = $this->diffDocs($d1, $d2);
        $tmp = array();
        foreach($diffs as $diff) {
            if (isset($reviewAttributes[$diff])) {
                $name = $reviewAttributes[$diff];
                if (!isset($tmp[$name])) {
                    $tmp[] = $name;
                }
            }
        }
        if (count($tmp) > 0) {
            return 'Attributes changed: [' . implode(',', $tmp) . ']';
        }
        else {
            return 'No changes.';
        }
    }
    
    protected function diffDocs($d1, $d2)
    {
        $diffs = array();
        foreach($d1 as $k => $v) {
            if (is_array($v)) {
                if (isset($d2[$k])) {
                    $tmp = $this->diffDocs($v, $d2[$k]);
                    if (count($tmp) > 0) {
                        $diffs[] = $k;
                    }
                }
                else {
                    $diffs[] = $k;
                }
            } else {
               if (isset($d2[$k])) {
                   if ($d2[$k] != $v) {
                       $diffs[] = $k;
                   }
               }
               else {
                    $diffs[] = $k;
               }
            }
        }
        return $diffs;
    }
}
?>
