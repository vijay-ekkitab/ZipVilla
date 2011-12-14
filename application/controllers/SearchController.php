<?php
include_once("ZipVilla/TypeConstants.php");
class SearchController extends Zend_Controller_Action
{
    const CHECKIN  = 'check_in';
    const CHECKOUT = 'check_out';
    const GUESTS   = 'guests';
    const QUERY    = 'query';
    const PAGE     = 'page';
    const SORT     = 'sort';
    const PRICE_RANGE = 'price_range';
    const KEYWORDS = 'keywords';
    const SHOWTAB  = 'showtab';
    
    protected $facets = array('amenities', 'onsite_services', 'suitability', 'address__location', 'shared');
    
    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('lookahead', 'html')
                    ->initContext();
    }
    
    protected function save_session_data($values)
    {
        $session = new Zend_Session_Namespace('guest_data');
        $session->values = $values;
    }
    
    protected function get_session_data()
    {
        $session = new Zend_Session_Namespace('guest_data');
        return $session->values;
    }
    
    protected function read_value($values, $index, $default=null)
    {
        return isset($values[$index]) ? $values[$index] : $default;
    }
    
    protected function set_view_params($values, $results, $q) 
    {
        $this->view->search_query   = $this->read_value($values, SearchController::QUERY);
        $this->view->checkin        = $this->read_value($values, SearchController::CHECKIN);
        $this->view->checkout       = $this->read_value($values, SearchController::CHECKOUT);
        $this->view->guests         = $this->read_value($values, SearchController::GUESTS);
        $this->view->page           = $this->read_value($values, SearchController::PAGE);
        $this->view->pagesz         = PAGE_SZ;
        $this->view->sortorder      = $this->read_value($values, SearchController::SORT);
        $this->view->price_range    = $this->read_value($values, SearchController::PRICE_RANGE);
        $this->view->keywords       = $this->read_value($values, SearchController::KEYWORDS);
        $this->view->showtab        = $this->read_value($values, SearchController::SHOWTAB, 0);
        $this->view->results        = $results['docs'];
        $this->view->total_hits     = $results['count'];
        if (isset($results['facets'])) {
            $this->view->facets     = $results['facets'];
        }
        unset($q['city_state']);
        $this->view->facets_selected = $q;
    }

    public function reindexAction() 
    {
        $im = $this->_helper->indexManager;
        $im->delete();
        $im->indexAll();
        $this->_helper->redirector('index', 'index');
    }
    
    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        
        $values = $this->getRequest()->getParams();
        
        if (!isset($values[SearchController::QUERY])) {
            $values = $this->get_session_data();
            if ($values == null) {
                $this->_helper->redirector('index', 'index');
            }
        }
        
        $sm = $this->_helper->searchManager;
            
        if ((!isset($values[SearchController::QUERY])) ||
            ($values[SearchController::QUERY] == '')) {
            $values[SearchController::QUERY] = '*';
        }

        if (!isset($values[SearchController::PRICE_RANGE]))
            $values[SearchController::PRICE_RANGE] = null;
        else {
            if(is_string($values[SearchController::PRICE_RANGE])) {
                if (preg_match_all('/([0-9]+)/', $values[SearchController::PRICE_RANGE], $matches)) {
                    $values[SearchController::PRICE_RANGE] = $matches[0];
                }
                else {
                    $values[SearchController::PRICE_RANGE] = array(MIN_RATE, MAX_RATE);
                }
            }
        }
        
        $q = array();
        
        if ($values[SearchController::QUERY] == '*') {
            $q = array('city_state' => $values[SearchController::QUERY]);
        }
        else {
            $q = array('city_state' => '"'.$values[SearchController::QUERY].'"');
        }
        
        foreach ($this->facets as $facet) {
            if (isset($values[$facet])) {
                if (!isset($q[$facet])) {
                    $q[$facet] = array();
                }
                foreach($values[$facet] as $v) {
                    $v = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $v);
                    $q[$facet][] = '"' . $v . '"';
                }
            }
        }
        
        if (isset($values[SearchController::KEYWORDS])) {
            $keyword_array = explode(' ', $values[SearchController::KEYWORDS]);
            for($i=0; $i<count($keyword_array); $i++) {
                $keyword_array[$i] = '"'.$keyword_array[$i].'"';
            }
            $q['description'] = $keyword_array;
        }
        
        $search_results = $sm->search($q, 
                                      $this->read_value($values, SearchController::CHECKIN),
                                      $this->read_value($values, SearchController::CHECKOUT),
                                      $this->read_value($values, SearchController::GUESTS,1),
                                      $this->read_value($values, SearchController::SORT,SORT_ORDER_RATING),
                                      $this->read_value($values, SearchController::PRICE_RANGE),
                                      $this->read_value($values, SearchController::PAGE,1),
                                      PAGE_SZ);
            
        if ($search_results) {
            $this->set_view_params($values, $search_results, $q);
        }
        else {
            $this->view->error = 'encountered error in search.';
        }
        $this->save_session_data($values);
    }

    
    public function lookaheadAction() {
        $logger = Zend_Registry::get('zvlogger');
        $results = array();
        $request = $this->getRequest();
        if ($request->isPost()) {
           $values = $request->getPost();
           $query = isset($values['query']) ? $values['query'] : '';
           if ($query != '') {
                $sm = $this->_helper->searchManager;
                $q = array('city_state' => strtolower($query)."*");
                $results = $sm->search_ajax($q);
                $this->lookahead = $results;
           }
        }
        $this->view->lookahead = $results;
    }
    

}

