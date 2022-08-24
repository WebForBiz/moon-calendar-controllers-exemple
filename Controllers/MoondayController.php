<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cookie;
use App;
use App\Moonday;
use App\Page;
use App\State;
use App\Moonphase;
use App\Category;
//use Illuminate\Support\Facades\Cookie;
use App\City;
use App\Country;
use App\Region;
use DateTime;

class MoondayController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function main() {

        
        
        $moonday = new Moonday();
        $today = $this->data['today_day'];
        $langid = $this->data['langid'];
        
        $page =  new Page();
        $page_now =  $page->where('id','=',28)->first();
         
        $states =  $moonday->fortunate($today, $this->data['langid']);

        foreach($states as $key => $val) {
            $states[$key] = array_slice($val, 0, 6);
        }
        
        app()->setLocale($this->data['langformoonday']);
        
        $this->data['blog'] = $page->where('lang_id',  $langid)->where('is_blog','=',1)->where('status','=',1)->orderBy('id', 'DESC')->limit(3)->get();
        $this->data['today'] = $moonday->where('id', $today)->where('lang_id',  $langid)->first(); //$moonday->find($today);
        $this->data['uMonth'] = $moonday->getUnnormalMonth(0, app()->getLocale());
        $this->data['states'] = $states;
        $this->data['allstates'] = $moonday->allstats($today);
        $this->data['seo_title'] = (isset($this->data['langtext']['main-seo-title'])) ? $this->data['langtext']['main-seo-title']: "Lunar calendar for today what is ✔ good and what ✖ is bad to do on this day."; //'Лунный календарь на сегодня  что ✔хорошо а что ✖плохо делать в этот день.';
        $this->data['seo_desc'] = (isset($this->data['langtext']['main-seo-desc'])) ? $this->data['langtext']['main-seo-desc']: "What to do today according to the lunar calendar? characteristic of the day for people - haircut, coloring other practices. The phase of the moon, in which sign of the zodiac is the moon on this day."; 'Что делать сегодня по лунному календарю? характеристика дня для людей - стрижка, окраска другие практики. Фаза Луны, в каком знаке Зодиака находится Луна в этот день.';
         
        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        
        
        return view('moondays.main', $this->data);
    }

    public function show($lang = 'en', $id = 0) {

        if(is_numeric($lang) && $id == 0) {
            $id = $lang;
            $lang = 'en';
        }
        
        $moonday = new Moonday();
        
        $langid = $this->data['langid'];
        
        $this->data['moonday'] = $moonday->where('id', $id)->where('lang_id',  $langid)->first(); //$moonday->find($id);
        
        if($this->data['moonday']) {
            
            $this->data['states'] = $moonday->fortunate($id,  $langid);
            $this->data['allstates'] = $moonday->allstats($id,  $langid);
            
            
            
            $phase = 1;
            if($id <= 8)  {
                $phase = 1;
            } elseif(($id > 8) && ($id <= 15)) {
                $phase = 2;
            } elseif(($id > 15) && ($id <= 23)) {
                $phase = 3;
            } elseif($id > 23) {
                $phase = 4;
            }
            
            $this->data['when'] = array();
            for($i=1;$i<120;$i++) {
                if($moonday->getMoonday(date('d-m-Y', mktime(0, 0, 0, date("m"), date("d")+$i, date("Y")))) == $id) {
                    $this->data['when'][] = ' '.date('j', mktime(0, 0, 0, date("m"), date("d")+$i, date("Y"))).' '.$moonday->getUnnormalMonth(date('m', mktime(0, 0, 0, date("m"), date("d")+$i, date("Y")))).' '.date('Y', mktime(0, 0, 0, date("m"), date("d")+$i, date("Y"))); //date('d-m-Y', mktime(0, 0, 0, date("m"), date("d")+$i, date("Y")));
                }
            }
            
            $this->data['when_short'] = ((isset($this->data['langtext']['moonday-when-next'])) ? $this->data['langtext']['moonday-when-next']: 'Next lunar day').' - '.$this->data['when'][0];
            unset($this->data['when'][0]);
            $this->data['when'] = implode(',',$this->data['when']);
            
            $this->data['seo_title'] = ($this->data['moonday']->seo_title) ? $this->data['moonday']->seo_title :  $id.' лунный день характеристика - '.$phase.' четверть Луны что ✔хорошо а что ✖плохо делать в этот день.'; //$this->data['moonday']->seo_title;
            $this->data['seo_desc'] = ($this->data['moonday']->seo_description) ? $this->data['moonday']->seo_description : 'Что делать в '.$id.' лунный день? характеристика дня для людей - стрижка, окраска другие практики. У кого день рождения в этот день';//$this->data['moonday']->seo_description;
            $this->data['seo_key'] = $this->data['moonday']->seo_keywords;
    
            
            foreach($this->data['langlist'] as $llang) {
                if($llang->alias != 'en') {
                    $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                    $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                }
                if($llang->alias == 'en') {
                    $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                    $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                }
                
            }
            
            return view('moondays.show', $this->data);
        }
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
    }

    public function showday($lang = 'en', $id = 0) {
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];

        app()->setLocale($lang);
        
        $day_raw = explode('-', $id);
        $moonday = new Moonday();
        $day = $day_raw[0];
        $year = $day_raw[2];
        $month = intval($day_raw[1]);
        $currentday =  $moonday->getMoonday($id);
        
        if($currentday) {
            $this->data['day'] = $day;
            $this->data['moonday'] = $moonday->where('id', $currentday)->where('lang_id',  $langid)->first(); //$moonday->find($currentday);
            $this->data['moonrise'] = $moonday->moonrise($id);
            $this->data['zodiac'] = $moonday->zodiac($id, app()->getLocale());
            $this->data['uMonth'] = $moonday->getUnnormalMonth($month, app()->getLocale());
            $this->data['year'] = $year;
            $this->data['states'] = $moonday->fortunate($currentday, $langid);
            $this->data['allstates'] = $moonday->allstats($currentday, $langid);
            
            $this->data['seo_title'] = ($this->data['moonday']->seo_title) ? $this->data['moonday']->seo_title : 'Лунный календарь на '.$day.' '.$moonday->getUnnormalMonth($month, app()->getLocale()).' '.$year.' года что ✔хорошо а что ✖плохо делать в этот день.';//'Лунный календарь на '.$day.' '.$moonday->getUnnormalMonth($month).' '.$year.' года: благоприятные и неблагоприятные дни';//$this->data['moonday']->seo_title;
            $this->data['seo_desc'] = ($this->data['moonday']->seo_description) ? $this->data['moonday']->seo_description : 'Что делать '.$day.' '.$moonday->getUnnormalMonth($month, app()->getLocale()).' '.$year.' года по лунному календарю? характеристика дня для людей - стрижка, окраска другие практики. Фаза Луны, в каком знаке Зодиака находится Луна в этот день.';//$this->data['moonday']->seo_description;
            $this->data['seo_key'] = $this->data['moonday']->seo_keywords;
   
            
            
            foreach($this->data['langlist'] as $llang) {
                if($llang->alias != 'en') {
                    $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                    $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                }
                if($llang->alias == 'en') {
                    $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                    $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                }
                
            }
            
            return view('moondays.showday', $this->data);
        }
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);

    }

    public function page($lang = 'en', $id = 0) {
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        app()->setLocale($lang);

        $page = new Page;
        $moonday = new Moonday;
        $state = new State;

        
        $this->data['week'] = $this->data['calendar'];
        $this->data['monthtitle'] = $moonday->getMonth(0, app()->getLocale());
        $this->data['unmonthtitle'] = $moonday->getWhereMonth(0, app()->getLocale());
        $this->data['page'] = $page->where('alias', $id)->where('lang_id',  $langid)->where('status','=',1)->first();
        
        if($this->data['page']) {
            $this->data['pagenext'] = $id;
            
            $state_raw = array();
            foreach($this->data['page']->states as $val) {
                
                $tstate = $state->where('id', $val->id)->where('lang_id',  $langid)->first();
                
                $state_raw[$val->alias]['title'] = $tstate->title;
                $state_raw[$val->alias]['alias'] = $val->alias;
                foreach($state->where('alias', $val->alias)->first()->moondays as $day) {
                    $state_raw[$val->alias][$day->pivot->moonday_id] = $day->pivot->toArray();
                    switch ($state_raw[$val->alias][$day->pivot->moonday_id]['lucky']) {
                        case 1:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-horrible'])) ? $this->data['langtext']['state-horrible']: "Horrible";
                            break;
                        case 2:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-bad'])) ? $this->data['langtext']['state-bad']: "Bad";
                            break;
                        case 4:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-good'])) ? $this->data['langtext']['state-good']: "Good";
                            break;
                        case 5:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-great'])) ? $this->data['langtext']['state-great']: "Great";
                            break;
                        default:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-average'])) ? $this->data['langtext']['state-average']: "Average";
                    }
                }
            }
            $this->data['state'] = $state_raw;
            $this->data['seo_title'] = $this->data['page']->seo_title.' на '.$this->data['monthtitle'].' '.date('Y');
            $this->data['seo_desc'] = $this->data['page']->seo_description;
            $this->data['seo_key'] = $this->data['page']->seo_keywords;
    
            
            $pagestest = $page->where('alias', $id)->where('status','=',1)->get();
            $llangs = array();
            foreach($pagestest as $val) {
                $llangs[$val->lang_id] = $val->lang_id;
            }
            
            foreach($this->data['langlist'] as $llang) {
                if(isset($llangs[$llang->id])) {
                    if($llang->alias != 'en') {
                        $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                        $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                    }
                    if($llang->alias == 'en') {
                        $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                        $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                    }
                }
            }
            
            return view('moondays.page', $this->data);
        }
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
    }
    
    public function pagenext($lang = 'en', $id = 0) {
        
        $uri = str_replace('pagenext', 'page', $_SERVER['REQUEST_URI']);
        header("Location: ".$uri, true, 301);
        exit();
        
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        app()->setLocale($lang);
       
        $page = new Page;
        $moonday = new Moonday;
        $state = new State;
        
        $type = 1; // or $type = -1
        $current_month = date('m');
        $nextmonth = date('m', mktime(0, 0, 0, $current_month + $type, 1, date("Y")));
        
        $this->data['week'] = $moonday->calendarnext();
        $this->data['monthtitle'] = $moonday->getMonth($nextmonth, app()->getLocale());
        $this->data['unmonthtitle'] = $moonday->getWhereMonth($nextmonth, app()->getLocale());
        $this->data['page'] = $page->where('alias', $id)->where('lang_id',  $langid)->first();
        
        if($this->data['page']) {
        
            $state_raw = array();
            foreach($this->data['page']->states as $val) {
                $tstate = $state->where('id', $val->id)->where('lang_id',  $langid)->first();
                $state_raw[$val->alias]['title'] = $tstate->title;
                $state_raw[$val->alias]['alias'] = $val->alias;
                foreach($state->where('alias', $val->alias)->first()->moondays as $day) {
                    $state_raw[$val->alias][$day->pivot->moonday_id] = $day->pivot->toArray();
                    switch ($state_raw[$val->alias][$day->pivot->moonday_id]['lucky']) {
                        case 1:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-horrible'])) ? $this->data['langtext']['state-horrible']: "Horrible";
                            break;
                        case 2:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-bad'])) ? $this->data['langtext']['state-bad']: "Bad";
                            break;
                        case 4:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-good'])) ? $this->data['langtext']['state-good']: "Good";
                            break;
                        case 5:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-great'])) ? $this->data['langtext']['state-great']: "Great";
                            break;
                        default:
                            $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-average'])) ? $this->data['langtext']['state-average']: "Average";
                    }
                }
            }
            $this->data['state'] = $state_raw;
            $this->data['seo_title'] = $this->data['page']->seo_title.' на '.$this->data['monthtitle'].' '.date('Y', mktime(0, 0, 0, $current_month + $type, 1, date("Y")));
            $this->data['seo_desc'] = $this->data['page']->seo_description;
            $this->data['seo_key'] = $this->data['page']->seo_keywords;
            $this->data['unnextmonthtitle'] = $moonday->getMonth($nextmonth, app()->getLocale());
            
            return view('moondays.pagenext', $this->data);
        }
        
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
    }

    public function blogindex() {
    
        $langid = $this->data['langid'];
        
    	$page = new Page;
    	$this->data['pages'] = $page->where('is_blog','=',1)->where('lang_id',  $langid)->where('status','=',1)->get();
    	
    	$this->data['seo_title'] = (isset($this->data['langtext']['header-blog'])) ? $this->data['langtext']['header-blog']: "Blog";
    	$this->data['seo_desc'] = (isset($this->data['langtext']['header-blog'])) ? $this->data['langtext']['header-blog']: "Blog";
    	$this->data['seo_key'] = (isset($this->data['langtext']['header-blog'])) ? $this->data['langtext']['header-blog']: "Blog";
    
    	
    	foreach($this->data['langlist'] as $llang) {
    	    if($llang->alias == 'ru') {
    	        $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
    	        $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
    	    }
    	}
    	
    	return view('moondays.blogindex', $this->data);
    
    }

    
    
    public function blog($lang = 'en', $id = 0) {
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        app()->setLocale($lang);
    
    	$page = new Page;
    	$moonday = new Moonday;
    	$state = new State;
    
    	$this->data['week'] = $this->data['calendar'];
    	$this->data['monthtitle'] = $moonday->getMonth(0, app()->getLocale());
    	$this->data['unmonthtitle'] = $moonday->getWhereMonth(0, app()->getLocale());
    	$this->data['page'] = $page->where('alias', $id)->where('lang_id',  $langid)->where('status','=',1)->first();
    	
    	if($this->data['page']) {
    	
        	$state_raw = array();
        	foreach($this->data['page']->states as $val) {
        	    $tstate = $state->where('id', $val->id)->where('lang_id',  $langid)->first();
        	    $state_raw[$val->alias]['title'] = $tstate->title;
        		$state_raw[$val->alias]['alias'] = $val->alias;
        		foreach($state->where('alias', $val->alias)->first()->moondays as $day) {
        			$state_raw[$val->alias][$day->pivot->moonday_id] = $day->pivot->toArray();
        			switch ($state_raw[$val->alias][$day->pivot->moonday_id]['lucky']) {
        			    case 1:
        			        $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-horrible'])) ? $this->data['langtext']['state-horrible']: "Horrible";
        			        break;
        			    case 2:
        			        $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-bad'])) ? $this->data['langtext']['state-bad']: "Bad";
        			        break;
        			    case 4:
        			        $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-good'])) ? $this->data['langtext']['state-good']: "Good";
        			        break;
        			    case 5:
        			        $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-great'])) ? $this->data['langtext']['state-great']: "Great";
        			        break;
        			    default:
        			        $state_raw[$val->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-average'])) ? $this->data['langtext']['state-average']: "Average";
        			}
        		}
        	}
        	$this->data['state'] = $state_raw;
        	$this->data['seo_title'] = $this->data['page']->seo_title;
        	$this->data['seo_desc'] = $this->data['page']->seo_description;
        	$this->data['seo_key'] = $this->data['page']->seo_keywords;
        
        	if($this->data['page']->in_menu == 3) {
        	    $this->data['blogmonthzodiac'] = true;
        	} else {
        	    $this->data['blogmonthzodiac'] = false;
        	}
        	
        	$pagestest = $page->where('alias', $id)->where('status','=',1)->get();
        	$llangs = array();
        	foreach($pagestest as $val) {
        	    $llangs[$val->lang_id] = $val->lang_id;
        	}
        	
        	foreach($this->data['langlist'] as $llang) {
        	    if(isset($llangs[$llang->id])) {
        	        if($llang->alias != 'en') {
        	            $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
        	            $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
        	        }
        	        if($llang->alias == 'en') {
        	            $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
        	            $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
        	        }
        	    }
        	}
        	
        	return view('moondays.blog', $this->data);
    	}
    	
    	$data['title'] = '404';
    	$data['name'] = 'Page not found';
    	return response()->view('errors.404',$this->data,404);
    }
    
    public function city($lang = 'en', $id = 0) {
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        app()->setLocale($lang);
        
        $page = new City;
        $moonday = new Moonday;
        $state = new State;
        
        $this->data['week'] = $this->data['calendar'];
        $this->data['monthtitle'] = $moonday->getMonth(0, app()->getLocale());
        $this->data['unmonthtitle'] = $moonday->getWhereMonth(0, app()->getLocale());
        $this->data['page'] = $page->where('alias', $id)->where('lang_id',  $langid)->where('status',  1)->first();
        
        if($this->data['page']) {
            $cities_raw = $page->where('lang_id',  $langid)->where('status',  1)->get();
            $cities_new = array();
            $rasst_old = 10000000;
            foreach($cities_raw as $key => $val) {
                $rasst = abs($this->data['page']->latitude - $val->latitude) + abs($this->data['page']->lontitude - $val->lontitude);
                $cities_new[$key]['title'] = $val->title;
                $cities_new[$key]['alias'] = $val->alias;
                $cities_new[$key]['rasst'] = $rasst;
            }
            
            usort($cities_new, function($a, $b){
                if ($a['rasst'] == $b['rasst']) {
                    return 0;
                }
                return ($a['rasst'] < $b['rasst']) ? -1 : 1;
            });
            
            $this->data['citiesrasst'] = array_slice($cities_new, 1, 4);
            //print_r('<pre>'); print_r($cities_new); exit();
        
        
       
            
            $moonday = new Moonday();
            $day = date("d");
            $year = date("Y");
            $month = date("m");
            $currentday =  $moonday->getMoonday($day.'-'.$month.'-'.$year);
            
            $this->data['seo_title'] = $this->data['page']->seo_title;
            $this->data['seo_desc'] = ($this->data['page']->seo_description) ? $this->data['page']->seo_description : 'Лунный календарь для '.$this->data['page']->title.' на сегодня. Благоприятные и не очень дни лунного календаря на '.date("d").' '.$moonday->getUnnormalMonth($month, app()->getLocale()).' '.date("Y").'.'; //$this->data['page']->seo_description;
            
            
            $this->data['day'] = $day;
            $this->data['moonday'] = $moonday->where('id', $currentday)->where('lang_id',  $langid)->first(); //$moonday->find($currentday);
            $this->data['uMonth'] = $moonday->getUnnormalMonth($month, app()->getLocale());
            $this->data['year'] = $year;
            $this->data['allstates'] = $moonday->allstats($currentday, $langid);
            
            
            $pagestest = $page->where('alias', $id)->where('status','=',1)->get();
            $llangs = array();
            foreach($pagestest as $val) {
                $llangs[$val->lang_id] = $val->lang_id;
            }
            
            foreach($this->data['langlist'] as $llang) {
                if(isset($llangs[$llang->id])) {
                    if($llang->alias != 'en') {
                        $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                        $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                    }
                    if($llang->alias == 'en') {
                        $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                        $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
                    }
                }
            }
            
            return view('moondays.city', $this->data);
        }
        
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
    }
    

    public function tomorrow($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        $moonday = new Moonday();
        $day = date("d")+1;
        $year = date("Y");
        $month = date("m");
        $currentday =  $moonday->getMoonday($day.'-'.$month.'-'.$year);


        $page =  new Page();
        $page_now =  $page->where('id','=',27)->where('lang_id',  $langid)->first();
        
         
        $this->data['day'] = $day;
        $this->data['moonday'] = $moonday->where('id', $currentday)->where('lang_id',  $langid)->first(); //$moonday->find($currentday);
        $this->data['uMonth'] = $moonday->getUnnormalMonth($month, app()->getLocale());
        $this->data['year'] = $year;
        $this->data['states'] = $moonday->fortunate($currentday, $langid);
        $this->data['allstates'] = $moonday->allstats($currentday, $langid);
        $this->data['seo_title'] = ($page_now->seo_title != '')? $page_now->seo_title: $moonday->where('id', $currentday)->where('lang_id',  $langid)->first()->seo_title; //$moonday->find($currentday)->seo_title;
        $this->data['seo_desc'] = ($page_now->seo_description != '')? $page_now->seo_description:  $moonday->where('id', $currentday)->where('lang_id',  $langid)->first()->seo_description; //$moonday->find($currentday)->seo_description;
        
        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.tomorrow', $this->data);

    }

    public function checkRait(Request $request)
    {
            $input = $request->only(['moonday','lucky']);
            $moonday = new Moonday();
            
            $langid = $this->data['langid'];

            if(isset($input['moonday']) && ($input['moonday'] >= 1) && ($input['moonday'] <= 30)) {
                $moonday = $moonday->where('id', $input['moonday'])->where('lang_id',  $langid)->first(); //$moonday->find($input['moonday']);

                $summ = ($moonday->lucky*$moonday->votes+$input['lucky'])/($moonday->votes+1);

                $moonday->where('id', $input['moonday'])
                    ->update(['lucky' => round($summ), 'votes' => ($moonday->votes+1)]);

                echo json_encode(array('summ' => round($summ),'votes' => ($moonday->votes+1), 'mess' => 'Благодарим за голос!'));
            }
    }

    public function newsletter(Request $request)
    {
        $moonday = new Moonday();
        $this->data['states'] = $moonday->statesList();

        return view('moondays.newsletter', $this->data);

    }

    public function month($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        $page = new Page;
        $year = date("Y");
        $this->data['week'] = $this->data['calendar'];
        $this->data['year'] = $year;
        $this->data['page'] = $page->where('alias', 'month')->where('lang_id',  $langid)->first();
        $this->data['seo_title'] = $this->data['page']->seo_title;
        $this->data['seo_desc'] = $this->data['page']->seo_description;
        $this->data['seo_key'] = $this->data['page']->seo_keywords;

        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.month', $this->data);
    }
    
    
    public function monthplace($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        $moonphase = new Moonphase();
        
        $page = new Page;
        $year = date("Y");
        
        $this->data['week'] = $this->data['calendar'];
        $this->data['year'] = $year;
        $this->data['page'] = $page->where('alias', 'monthplace')->where('lang_id',  $langid)->first();
        $this->data['seo_title'] = $this->data['page']->seo_title;
        $this->data['h1'] = $this->data['page']->h1;
        $this->data['seo_desc'] = $this->data['page']->seo_description;
        $this->data['seo_key'] = $this->data['page']->seo_keywords;
        
        
        $city_coords = $this->data['current_city'];
        $latitude = $city_coords->latitude;
        $lontitude = $city_coords->lontitude;
        
        foreach($this->data['calendar'] as $key => $val) {
            foreach($val as $key1 => $val1) {
                if(isset($val1['day'])) {
                    $this->data['calendar'][$key][$key1]['phase'] = $moonphase->getPhaseDesc($val1['day'].'-'.date('m').'-'.date('Y'), app()->getLocale());
                    
                    $calc = new SunCalc(new DateTime($val1['day'].'-'.date('m').'-'.date('Y')), $latitude, $lontitude);
                    $moonsunset = $calc->getMoonTimes();
                    
                    $moonrisse = false;
                    $moonsset = false;
                    if(isset($moonsunset['moonrise'])) {
                        $moonrisse = date_format($moonsunset['moonrise'],'H:i');
                    }
                    if(isset($moonsunset['moonset'])) {
                        $moonsset = date_format($moonsunset['moonset'],'H:i');
                    }
                    
                    $this->data['calendar'][$key][$key1]['rise'] = $moonrisse;
                    $this->data['calendar'][$key][$key1]['set'] = $moonsset;
                    
                    
                   // print_r('<pre>'); print_r($this->data['calendar'][$key][$key1]); exit();
                }
            }
        }
        //$this->data['moonphases'] = 
        
        
        
        if(isset($_GET['lat'])) {
            
            $lang = ($_GET['lang'])? $_GET['lang']: 'en';
            
            app()->setLocale($lang);
            
            include('/var/www/moonphasecalendar.org/language/'.app()->getLocale().'.php');
            
            $this->data['langtext'] = $langtext;
            
            $lat = ($_GET['lat'])? $_GET['lat']: 1;
            $lon = ($_GET['lon'])? $_GET['lon']: 1;
            $month = ($_GET['month'])? $_GET['month']: 1;
            $year = ($_GET['year'])? $_GET['year']: 1;
            
            
            $moonday = new Moonday();
            $moonphase = new Moonphase();
            $page = new Page;
            
            $this->data['week'] = $moonday->calendarByMonth($year, $month);
            $this->data['year'] = $year;
            $this->data['page'] = $page->where('alias', 'monthplace')->where('lang_id',  $langid)->first();
            $this->data['seo_title'] = $this->data['page']->seo_title;
            $this->data['seo_desc'] = $this->data['page']->seo_description;
            $this->data['seo_key'] = $this->data['page']->seo_keywords;
           
            $latitude = $lat;
            $lontitude = $lon;
            
            foreach($this->data['week'] as $key => $val) {
                foreach($val as $key1 => $val1) {
                    if(isset($val1['day'])) {
                        $this->data['week'][$key][$key1]['phase'] = $moonphase->getPhaseDesc($val1['day'].'-'.date('m').'-'.date('Y'), app()->getLocale());
                        
                        $calc = new SunCalc(new DateTime($val1['day'].'-'.date('m').'-'.date('Y')), $latitude, $lontitude);
                        $moonsunset = $calc->getMoonTimes();
                        
                        $moonrisse = false;
                        $moonsset = false;
                        if(isset($moonsunset['moonrise'])) {
                            $moonrisse = date_format($moonsunset['moonrise'],'H:i');
                        }
                        if(isset($moonsunset['moonset'])) {
                            $moonsset = date_format($moonsunset['moonset'],'H:i');
                        }
                        
                        $this->data['week'][$key][$key1]['rise'] = $moonrisse;
                        $this->data['week'][$key][$key1]['set'] = $moonsset;
                        
                        
                        // print_r('<pre>'); print_r($this->data['calendar'][$key][$key1]); exit();
                    }
                }
            }
            
            //print_r('<pre>'); print_r($this->data['week']); exit();
            
            $text = '
                        <table class="table pure-table pure-table-bordered month-calendar text-center">
                            <tbody>
                            <tr>
                                    	<th class="mon">'.((isset($this->data['langtext']['state-mon']))? $this->data['langtext']['state-mon'] : "Mon").'</th>
                                    	<th class="tue">'.((isset($this->data['langtext']['state-tue']))? $this->data['langtext']['state-tue'] : "Tue").'</th>
                                    	<th class="wed">'.((isset($this->data['langtext']['state-wed']))? $this->data['langtext']['state-wed'] : "Wed").'</th>
                                    	<th class="thu">'.((isset($this->data['langtext']['state-thu']))? $this->data['langtext']['state-thu'] : "Thu").'</th>
                                    	<th class="fri">'.((isset($this->data['langtext']['state-fri']))? $this->data['langtext']['state-fri'] : "Fri").'</th>
                                    	<th class="sat">'.((isset($this->data['langtext']['state-sat']))? $this->data['langtext']['state-sat'] : "Sat").'</th>
                                    	<th class="sun">'.((isset($this->data['langtext']['state-sun']))? $this->data['langtext']['state-sun'] : "Sun").'</th></tr>';
            foreach($this->data['week'] as $i => $val) {
                $text .= '<tr>';
                for($j = 0; $j < 7; $j++) {
                    if(!empty($val[$j])) {
                        $text .= '<td style="position: relative;">
                                	<a  style="position: absolute; right: 10px; top: 5px;" href="'.$this->data['langprefixslash'].'/day/'.$val[$j]['day'].'-'.date('m-Y', time()).'">'.$val[$j]['day'].'</a>
                                	<a href="'.$this->data['langprefixslash'].$val[$j]['phase']['href'].'">
                                		<div class="phase-menu-img" style="background: url(\'/images/moonphases/'.$val[$j]['phase']['image'].'\')"></div><br/>'.$val[$j]['phase']['text'].'<br/>
                                		'.$val[$j]['rise'].' - '.$val[$j]['set'].'
                                	</a><br/>
                                	<a href="'.$this->data['langprefixslash'].'/moonday/'.$val[$j]['moon'].'">'.$val[$j]['moon'].' '.((isset($this->data['langtext']['monthplace-day']))? $this->data['langtext']['monthplace-day'] : "day").'</a>
                                </td>';
                    } else {
                        $text .= '<td>&nbsp;</td>';
                    }
                }
                $text .= '</tr>';
            }
            $text .= '      </tbody>
                        </table>
                    ';
            
            
            echo json_encode($text);
            exit();
        }
        
        
        
        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.monthplace', $this->data);
    }
    
    public function monthplacevariants() {
        $url='http://api.geonames.org/postalCodeSearchJSON?postalcode='.$_GET['zip'].'&maxRows=20&username=atpluscompany';
        //$url='http://api.geonames.org/postalCodeSearchJSON?postalcode=65111&maxRows=100&username=sairinbaz';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        
        $result=curl_exec($ch);
        
        curl_close($ch);
        
        $decode = json_decode($result,true);
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($decode['postalCodes']);
        
        die(); // даём понять, что обработчик закончил выполнение
    }
    

    public function week($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        $page = new Page;
        $year = date("Y");
        $day = date("j");
        $week = array();

        foreach($this->data['calendar'] as $key => $val) {
            for($j = 0; $j < 7; $j++) {
                if(!empty($val[$j]) && ($val[$j]['day'] == $day)) {
                    $week = $val;
                }
            }
        }

        $this->data['week'] = $week;
        $this->data['year'] = $year;
        $this->data['page'] = $page->where('alias', 'week')->where('lang_id',  $langid)->first();
        $this->data['seo_title'] = $this->data['page']->seo_title;
        $this->data['seo_desc'] = $this->data['page']->seo_description;
        $this->data['seo_key'] = $this->data['page']->seo_keywords;

        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.week', $this->data);
    }

    
    public function moonphasesindex($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        
        $this->data['seo_title'] =  $this->data['langtext']['header-phases']; //'Фазы луны';
        $this->data['seo_desc'] = $this->data['langtext']['header-phases'];
        $this->data['seo_key'] = 'Фазы луны';
        
        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.moonphasesindex', $this->data);
        
    }
    
    
    public function moongeoindex($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        
        $page = new City;
        $this->data['pages'] = $page->where('lang_id',  $langid)->where('status',  1)->get();
        
        $this->data['seo_title'] = $this->data['langtext']['moongeo-seo_title'];
        $this->data['seo_desc'] = $this->data['langtext']['moongeo-seo_desc'];
        $this->data['seo_key'] = 'География луны';
        
        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.moongeoindex', $this->data);
        
    }
    
    public function moonzodiacindex($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        
        $this->data['seo_title'] = $this->data['langtext']['header-zodiac']; //'Луна по знакам зодиака';
        $this->data['seo_desc'] = $this->data['langtext']['header-zodiac']; //'Луна по знакам зодиака';
        $this->data['seo_key'] = $this->data['langtext']['header-zodiac']; //'Луна по знакам зодиака';
        
        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.moonzodiacindex', $this->data);
        
    }
    
    public function sitemap($lang = 'en') {
        
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        //print_r($this->data['langlist']);
        
        $page =  new Page();
        $state = new State();
        $moonphase = new Moonphase();
        $moonday = new Moonday();
        $category = new Category();
        
        $this->data['sitemapday'] = date('Y-m-d');
        $this->data['sitemapmonth'] = date('Y-m').'-01';
        /*
        $this->data['sitemappages'] = //$page->where('is_blog','!=',1)->where('status','=',1)->where('lang_id',  $langid)->get();
        $this->data['sitemapblog'] = //$page->where('is_blog','=',1)->where('status','=',1)->where('lang_id',  $langid)->get();
        $this->data['sitemapstates'] = //$state->where('lang_id',  $langid)->where('status','=',1)->get();
        $this->data['sitemapmoonphases'] = //$moonphase->where('lang_id',  $langid)->get();
        $this->data['sitemapmoondays'] = //$moonday->where('lang_id',  $langid)->get();
        $this->data['sitemapcategories'] = //$category->where('lang_id',  $langid)->where('status','=',1)->get();
        */
        foreach($this->data['langlist'] as $val) {
            $langid = $val['id'];
            $this->data['sitemap'][$val['id']]['sitemappages'] = $page->where('is_blog','!=',1)->where('status','=',1)->where('lang_id',  $langid)->get();
            $this->data['sitemap'][$val['id']]['sitemapblog'] = $page->where('is_blog','=',1)->where('status','=',1)->where('lang_id',  $langid)->get();
            $this->data['sitemap'][$val['id']]['sitemapstates'] = $state->where('lang_id',  $langid)->where('status','=',1)->get();
            $this->data['sitemap'][$val['id']]['sitemapmoonphases'] = $moonphase->where('lang_id',  $langid)->get();
            $this->data['sitemap'][$val['id']]['sitemapmoondays'] = $moonday->where('lang_id',  $langid)->get();
            $this->data['sitemap'][$val['id']]['sitemapcategories'] = $category->where('lang_id',  $langid)->where('status','=',1)->get();
            if($val['alias'] != 'en') {
                $this->data['sitemap'][$val['id']]['langprefixslash'] = '/'.$val['alias'];
            } else {
                $this->data['sitemap'][$val['id']]['langprefixslash'] = '';
            }
        }
        
        //print_r($this->data['langlist']);
        
        return response(view('moondays.sitemap', $this->data))->header('Content-Type', 'text/xml');
    }
    
    public function locations(Request $request) {
        
        $country_id = $request->input('country_id', 1);
        $region_id = $request->input('region_id');
        
        
        $city = new City();
        $region = new Region();
        
        if(isset($country_id) && $country_id != '') {
            $regions = $region->where('country_id', $country_id)->get();
        } 
        if(isset($region_id) && $region_id != '') {
            $cities = $city->where('region_id', $region_id)->get();
        } else {
            foreach($regions as $reg) {
                $first_region = $reg->id;
                break;
            }
            $cities = $city->where('region_id', $first_region)->get();
        }
        
       
        echo json_encode(array('regions' => $regions,'cities' => $cities));
    }
    
    public function birthday($lang = 'en', Request $request) {
        
        app()->setLocale($lang);
        
        
        $langid = $this->data['langid'];
        
        $page =  new Page();
        $this->data['page'] =  $page->where('id','=',30)->where('lang_id',  $langid)->first();
        
        if($request->input('addres')) {
            
            
            $lang = $request->input('lang', 'en');
            
            if($lang == '')
                $lang = 'en';
            
            $langid = $request->input('langid', 1);
            
            app()->setLocale($lang);
            
            include('/var/www/moonphasecalendar.org/language/'.app()->getLocale().'.php');
            
            $this->data['langtext'] = $langtext;
            
            $this->data['langprefixslash'] = (app()->getLocale() != "en")? '/'.app()->getLocale(): '';
            
            $day = $request->input('day', 1);
            $month = $request->input('month', 1);
            $year = $request->input('year', 1900);
            
            
            $moonday = new Moonday();
            $moonphase = new Moonphase();
            $today = intval($moonday->getTodayMoonday());
            
            $text = '<h2 style="margin-top: 20px;">'.$this->data['langtext']['header-lunar-for'].' '.$day.'.'.$month.'.'.$year.' '.$this->data['langtext']['birthday-for'].' '.$request->input('addres').'</h2>';
            $text .= '<br/>';
            
            $moonrise = $moonday->moonrise($day.'-'.$month.'-'.$year, $request->input('lat'), $request->input('lng'));
            $today_day = $moonday->getMoonday($day.'-'.$month.'-'.$year);
            //$phase = $moonphase->getPhaseDesc();
            
            //print_r($phase);
            
            $phase = $moonphase->getPhaseDesc($day.'-'.$month.'-'.$year, app()->getLocale());
            
            //print_r($phase);
            //exit();
            $text .= '<p class="birthday-location-p">';
            if($moonrise['real']) {
                $text .= ''.$this->data['langtext']['header-before'].' '.$moonrise['value'];
                $tt = $today_day - 1;
                $text .= ' <a href="'.$this->data['langprefixslash'].'/moonday/'.$tt.'" title="'.$tt.' '.$this->data['langtext']['bread-lunar-day'].'">'.$tt.'</a>, '.$this->data['langtext']['header-next'].' ';
            }
            
            $text .= '<a href="'.$this->data['langprefixslash'].'/moonday/'.$today_day.'" title="'.$today_day.' '.$this->data['langtext']['bread-lunar-day'].'">'.$today_day.'</a> '.$this->data['langtext']['header-lunar-day'].'. ';
            $text .= '<br/>';
            $text .= $moonday->zodiac($day.'-'.$month.'-'.$year, app()->getLocale());
            $text .= '<br/>';
            
            $text .= ''.$this->data['langtext']['header-phase'].' <a href="'.$this->data['langprefixslash'].''.$phase['href'].'">'.$phase['text'].'</a>.';
            $text .= '</p>';
            $text .= '<br/>';
            
            $text .= '<h2>'.$this->data['langtext']['birthday-more-1'].' '.$today_day.' '.$this->data['langtext']['birthday-more-2'].'.</h2>';
            
            $text .= '<br/>';
            
            $moonday = $moonday->where('id', $today_day)->where('lang_id',  $langid)->first(); //$moonday->find($today_day);
            $text .= $moonday->description;
            $text .= '<a href="'.$this->data['langprefixslash'].'/moonday/'.$today_day.'"  class="birthday-location-button" >'.$this->data['langtext']['birthday-more'].' →</a>';
            $text .= '<br/>';
            
            
            echo json_encode($text);
            exit();
        }
        
        foreach($this->data['langlist'] as $llang) {
            if($llang->alias != 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org/'.$llang->alias.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            if($llang->alias == 'en') {
                $this->data['hreflangs'][$llang->hreflang]['href'] = 'https://moonphasecalendar.org'.$this->data['linknolang'];
                $this->data['hreflangs'][$llang->hreflang]['lang'] = $llang->hreflang;
            }
            
        }
        
        return view('moondays.birthday', $this->data);
    }
    
    public function sendMessage($chatID, $messaggio, $token) {
        echo "sending message to " . $chatID . "\n";
        
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatID;
        //$url = $url . "&parse_mode=HTML";
        $url = $url . "&text=" . urlencode(strip_tags(str_replace('&nbsp;', '', $messaggio)));
        $url = $url . "&parse_mode=Markdown";
        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    
    public function telegrambot($lang = 'en') {
       
        app()->setLocale($lang);
        
        $langid = $this->data['langid'];
        
        $zodiacCode = [
            'ar' => '♈',
            'aq' => '♒',
            'ge' => '♊',
            'sa' => '♐',
            'cp' => '♑',
            'vi' => '♍',
            'pi' => '♓',
            'sc' => '♏',
            'li' => '♎',
            'cn' => '♋',
            'ta' => '♉',
            'le' => '♌',
        ];
        
        $zodiac = str_replace('.', '', $this->data['zodiac']).' '.$zodiacCode[$this->data['zodiaccode']];
        
        $first_text = '';
        if($this->data['moonrise']['real']) {
            $first_text .= $this->data['langtext']['telegrambot-before'].' '.$this->data['moonrise']['value'].' '.($this->data['today_day'] - 1).' , '.$this->data['langtext']['telegrambot-then'].' ';
        }
        $first_text .= $this->data['today_day'].' '.$this->data['langtext']['telegrambot-moonday'].' 📅. ';
        
        $text = $first_text." ".$this->data['langtext']['telegrambot-in-phase']." ".$this->data['phase']['text']." ".$this->data['phase']['botimage'].".";
        $text .= "\n";
        $text .= $zodiac.' — '.$this->data['langtext']['telegrambot-visible'].' '.round($this->data['moonfraction']['fraction']*100).'%.';
        $text .= "\n";
        $text .= $this->data['langtext']['telegrambot-moonrise'].' '.$this->data['moonrisse'].'.';
        $text .= "\n";
        $text .= $this->data['langtext']['telegrambot-moonset'].' '.$this->data['moonsset'].'.';
        $text .= "\n";
        
        
        $text .= $this->data['langtext']['telegrambot-today']." -  ".date('d', time())." ".$this->data['langtext']['arr'][date('n', time())-1]." ".date('Y', time())." ".$this->data['langtext']['telegrambot-year'].".<br/>";
        $text .= "\n";
        $text .= $this->data['langtext']['telegrambot-character'].' '.$this->data['today_day'].$this->data['langtext']['telegrambot-of-day'];
        $text .= "\n";
        
        $text .= "\n";
        $text .= $this->data['langtext']['telegrambot-magic'].' '.$this->data['today_day'].' '.$this->data['langtext']['telegrambot-moon-day'];
        $text .= "\n";
        $text .= '['.$this->data['langtext']['telegrambot-more'].'](https://moonphasecalendar.org'.$this->data['langprefixslash'].'/moonday/'.$this->data['today_day'].'?'.$this->data['langtext']['telegrambot-utm'].')';
        
        
        $token = $this->data['langtext']['telegrambot-token'];
        $chatid = $this->data['langtext']['telegrambot-chatid'];
        
        //print_r($text); exit();
        if($token && $chatid) {
        	$this->sendMessage($chatid, $text, $token);
        }
        
        die('Done!');
    }
}
