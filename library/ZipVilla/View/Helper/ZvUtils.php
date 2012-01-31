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
}
?>
