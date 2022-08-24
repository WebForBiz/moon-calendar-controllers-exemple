<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\State;
use App\Category;
use App\Moonday;
use App\MoondayState;

class StateController extends BaseController
{
    //

    public function show($lang = en, $id = 0) {
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];

        app()->setLocale($lang);
        
        $state = new State();
        $moonday = new Moonday();
        $week = $this->data['calendar'];
        $year = date("Y");
        $month = date("m");
        $mstate = new MoondayState();
        $stat = $state->where('alias','LIKE',$id)->where('lang_id',  $langid)->where('status','=',1)->first();

        if($stat) {
            $statarray = $stat->toArray();
            $l = 'month_'.date("n");
            
            if($statarray[$l] == 0) {
                $flag = false;
                for($i=date("n"); $i<13; $i++) {
                    if($statarray['month_'.$i] == 1 && !$flag) {
                        $month = $i;
                        $flag = true;
                    }
                }
                if(!$flag) {
                    for($i=1; $i<13; $i++) {
                        if($statarray['month_'.$i] == 1 && !$flag) {
                            $month = $i;
                            $flag = true;
                            $year=$year+1;
                        }
                    }
                }
                if(mb_strlen($month) < 2) $month = '0'.$month;
                $week = $moonday->calendarByMonth($year,$month);
            }
            
            
            
            $states = $stat->moondays;
            foreach($states as $key => $val) {
                $states[$key]['nextmoonay'] = $state->nextmoonday($val->id);
            }
    
            $state_raw = array();
            foreach($stat->moondays as $day) {
                
                $ill = $day->pivot->toArray();
                
                $stateill = $mstate->where('state_id','=',$ill['state_id'])->where('moonday_id','=',$ill['moonday_id'])->where('lang_id',  $langid)->first()->toArray();
                
                //print_r('<pre>'); print_r($stateill); exit();
                
                $state_raw[$stat->alias][$day->pivot->moonday_id] = $stateill;
                switch ($state_raw[$stat->alias][$day->pivot->moonday_id]['lucky']) {
                    case 1:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-horrible'])) ? $this->data['langtext']['state-horrible']: "Horrible";
                        break;
                    case 2:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-bad'])) ? $this->data['langtext']['state-bad']: "Bad";
                        break;
                    case 4:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-good'])) ? $this->data['langtext']['state-good']: "Good";
                        break;
                    case 5:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-great'])) ? $this->data['langtext']['state-great']: "Great";
                        break;
                    default:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-average'])) ? $this->data['langtext']['state-average']: "Average";
                }
            }
    
            $states = array();
            for($i = 0; $i < count($week); $i++) {
                for($j = 0; $j < 7; $j++) {
                    if(isset($week[$i][$j]['day'])) {
                        $states[$week[$i][$j]['day']] = array(
                            'day' => $week[$i][$j]['moon'],
                            'lucky' => $state_raw[$stat->alias][$week[$i][$j]['moon']]['lucky']
                        );
                    }
                }
            }
    
            $uMonth = $moonday->getUnnormalMonth($month, app()->getLocale());
            $luckydays = array(
                '1' => '',
                '2' => '',
                '3' => '',
                '4' => '',
                '5' => ''
            );
            foreach($states as $key => $val) {
                if($val['lucky'] == 5) {
                    $luckydays[5] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 4) {
                    $luckydays[4] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 3) {
                    $luckydays[3] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 2) {
                    $luckydays[2] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 1) {
                    $luckydays[1] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
            }
            $luckydays[5] = substr($luckydays[5], 0, -2);
            $luckydays[4] = substr($luckydays[4], 0, -2);
            $luckydays[3] = substr($luckydays[3], 0, -2);
            $luckydays[2] = substr($luckydays[2], 0, -2);
            $luckydays[1] = substr($luckydays[1], 0, -2);
    
            $this->data['state'] = $stat;
            $this->data['states'] = $states;
            $this->data['week'] = $week;
            $this->data['monthtitle'] = $moonday->getMonth($month, app()->getLocale());
            $this->data['state_raw'] = $state_raw;
            $this->data['luckydays'] = $luckydays;
            $this->data['shownext'] = $id;
            $this->data['year'] = $year;
            
            
            $states = $state->where('category_id','=',$stat->category_id)->where('lang_id',  $langid)->where('lang_id',  $langid)->get();
            $dop_states = array();
            $i = 0;
            foreach($states as $key => $val) {
                if(($val->alias != $id) && ($i <3)) {
                    if($val->image) {
                        $val['image'] = $val->category->image;
                    } else {
                        $val['image'] = '/images/image/noimg.jpg';
                    }
                    
                    $val['catstat'] = $val->alias;
                    $dop_states[$val->title][] = $val;
                    $i++;
                }
            }
            
            $this->data['dop_states'] = $dop_states;
            
            
            
            $this->data['seo_title'] = $this->data['state']->seo_title .' '.((isset($this->data['langtext']['state-for'])) ? $this->data['langtext']['state-for']: "for").' '. $moonday->getMonth($month, app()->getLocale()) .' '.date('Y', time());
            $this->data['seo_desc'] = $this->data['state']->seo_description;
            $this->data['seo_key'] = $this->data['state']->seo_keywords;
    
            $type = 1; // or $type = -1
            $current_month = $month;//date('m');
            
            $nexttmonth = (intval($current_month)+$type);
            if($nexttmonth > 12)
                $nexttmonth = $nexttmonth - 12;
            
            if($statarray['month_'.$nexttmonth] == 0) {
                $date_now_int = $nexttmonth;
                $flag = false;
                for($i=$date_now_int; $i<13; $i++) {
                    if($statarray['month_'.$i] == 1 && !$flag) {
                        $month = $i;
                        $flag = true;
                    }
                }
                if(!$flag) {
                    for($i=1; $i<13; $i++) {
                        if($statarray['month_'.$i] == 1 && !$flag) {
                            $month = $i;
                            $flag = true;
                            $year=$year+1;
                        }
                    }
                }
                if(mb_strlen($month) < 2) $month = '0'.$month;
            } else {
                $month = date('m', mktime(0, 0, 0, ($nexttmonth), 1, $year));
            }
            
            $nextmonth = date('m', mktime(0, 0, 0, $month, 1, $year));
            
            $this->data['unnextmonthtitle'] = $moonday->getMonth($month, app()->getLocale());
            $this->data['unnextyear'] = $year = date('Y', mktime(0, 0, 0, $month, 1, $year));;
            
            $pagestest = $state->where('alias','LIKE',$id)->where('status','=',1)->get();
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
            
            
            return view('moondays.state', $this->data);
        }
        
        
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);

    }
    
    public function shownext($lang = en, $id = 0) {
        
        $uri = str_replace('statenext', 'state', $_SERVER['REQUEST_URI']);
        header("Location: ".$uri, true, 301);
        exit();
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        
        app()->setLocale($lang);
        
        $state = new State();
        $moonday = new Moonday();
        $mstate = new MoondayState();
        $type = 1; // or $type = -1
        $current_month = date('m');
        $nextmonth = date('m', mktime(0, 0, 0, $current_month + $type, 1, date("Y")));
        
        $week = $moonday->calendarnext();
        $year = date('Y', mktime(0, 0, 0, date('m') + 1, 1, date("Y")));
        $month = date('m', mktime(0, 0, 0, date('m') + 1, 1, date("Y")));
        
        $stat = $state->where('alias','LIKE',$id)->where('lang_id',  $langid)->first();
         
        if($stat) {
            
            $statarray = $stat->toArray();
            $l = 'month_'.date("n");
            
            if($statarray[$l] == 0 || $statarray['month_'.date('n')]) {
                $flag = false;
                $month = date('n');
                for($i=date("n"); $i<13; $i++) {
                    if($statarray['month_'.$i] == 1 && !$flag) {
                        $month = $i;
                        $flag = true;
                    }
                }
                if(!$flag) {
                    for($i=1; $i<13; $i++) {
                        if($statarray['month_'.$i] == 1 && !$flag) {
                            $month = $i;
                            $flag = true;
                            $year=$year+1;
                        }
                    }
                }
                
                $nexttmonth = (intval($current_month)+$type);
                if($nexttmonth > 12)
                    $nexttmonth = $nexttmonth - 12;
                
                $flag = false;
                if($statarray['month_'.$nexttmonth] == 0) {
                    $date_now_int = intval($month)+$type;
                    $flag = false;
                    for($i=$date_now_int; $i<13; $i++) {
                        if($statarray['month_'.$i] == 1 && !$flag) {
                            $month = $i;
                            $flag = true;
                        }
                    }
                    if(!$flag) {
                        for($i=1; $i<13; $i++) {
                            if($statarray['month_'.$i] == 1 && !$flag) {
                                $month = $i;
                                $flag = true;
                                $year=$year+1;
                            }
                        }
                    }
                    if(mb_strlen($month) < 2) $month = '0'.$month;
                } else {
                    $month = date('m', mktime(0, 0, 0, $month + 1, 1, $year));
                }
                
                if(mb_strlen($month) < 2) $month = '0'.$month;
                $week = $moonday->calendarByMonth($year,$month);
                $nextmonth = $month;
            }
            
            
            $states = $stat->moondays;
            foreach($states as $key => $val) {
                $states[$key]['nextmoonay'] = $state->nextmoonday($val->id);
            }
            
            $state_raw = array();
            foreach($stat->moondays as $day) {
                
                $ill = $day->pivot->toArray();
                
                $stateill = $mstate->where('state_id','=',$ill['state_id'])->where('moonday_id','=',$ill['moonday_id'])->where('lang_id',  $langid)->first()->toArray();
                
                //print_r('<pre>'); print_r($stateill); exit();
                
                $state_raw[$stat->alias][$day->pivot->moonday_id] = $stateill;
                switch ($state_raw[$stat->alias][$day->pivot->moonday_id]['lucky']) {
                    case 1:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-horrible'])) ? $this->data['langtext']['state-horrible']: "Horrible";
                        break;
                    case 2:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-bad'])) ? $this->data['langtext']['state-bad']: "Bad";
                        break;
                    case 4:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-good'])) ? $this->data['langtext']['state-good']: "Good";
                        break;
                    case 5:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-great'])) ? $this->data['langtext']['state-great']: "Great";
                        break;
                    default:
                        $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-average'])) ? $this->data['langtext']['state-average']: "Average";
                }
            }
            
            $states = array();
            for($i = 0; $i < count($week); $i++) {
                for($j = 0; $j < 7; $j++) {
                    if(isset($week[$i][$j]['day'])) {
                        $states[$week[$i][$j]['day']] = array(
                            'day' => $week[$i][$j]['moon'],
                            'lucky' => $state_raw[$stat->alias][$week[$i][$j]['moon']]['lucky']
                        );
                    }
                }
            }
            
            $uMonth = $moonday->getUnnormalMonth($month);
            $luckydays = array(
                '1' => '',
                '2' => '',
                '3' => '',
                '4' => '',
                '5' => ''
            );
            foreach($states as $key => $val) {
                if($val['lucky'] == 5) {
                    $luckydays[5] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 4) {
                    $luckydays[4] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 3) {
                    $luckydays[3] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 2) {
                    $luckydays[2] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
                if($val['lucky'] == 1) {
                    $luckydays[1] .= '<li>'.$key.' '.$uMonth.' ('.$val['day'].((isset($this->data['langtext']['state-nnth'])) ? $this->data['langtext']['state-nnth']: " lunar day").')</li>';
                }
            }
            
            $luckydays[5] = substr($luckydays[5], 0, -2);
            $luckydays[4] = substr($luckydays[4], 0, -2);
            $luckydays[3] = substr($luckydays[3], 0, -2);
            $luckydays[2] = substr($luckydays[2], 0, -2);
            $luckydays[1] = substr($luckydays[1], 0, -2);
            
            $this->data['state'] = $stat;
            
            if($this->data['state']->nexth1 == '') {
                $this->data['state']->nexth1 =  $this->data['state']->h1;
            }
            if($this->data['state']->nextseo_title == '') {
                $this->data['state']->nextseo_title =  $this->data['state']->seo_title;
            }
            if($this->data['state']->nextseo_description == '') {
                $this->data['state']->nextseo_description =  $this->data['state']->seo_description;
            }
           
            $this->data['states'] = $states;
            $this->data['week'] = $week;
            $this->data['monthtitle'] = $moonday->getMonth(0, app()->getLocale());
            $this->data['state_raw'] = $state_raw;
            $this->data['luckydays'] = $luckydays;
            $this->data['year'] = $year;
            
            $states = $state->where('category_id','=',$stat->category_id)->where('lang_id',  $langid)->get();
            $dop_states = array();
            $i = 0;
            foreach($states as $key => $val) {
                if(($val->alias != $id) && ($i <3)) {
                    if($val->image) {
                        $val['image'] = $val->category->image;
                    } else {
                        $val['image'] = '/images/image/noimg.jpg';
                    }
                    
                    $val['catstat'] = $val->alias;
                    $dop_states[$val->title][] = $val;
                    $i++;
                }
            }
            
            $this->data['dop_states'] = $dop_states;
            $this->data['seo_title'] = $this->data['state']->nextseo_title .' '.((isset($this->data['langtext']['state-for'])) ? $this->data['langtext']['state-for']: "for").' '. $moonday->getMonth($nextmonth, app()->getLocale()) .' '.date('Y', mktime(0, 0, 0, date('m') + 1, 1, date("Y")));
            $this->data['seo_desc'] = $this->data['state']->nextseo_description;
            $this->data['seo_key'] = $this->data['state']->seo_keywords;
            
            $this->data['unnextmonthtitle'] = $moonday->getMonth($nextmonth, app()->getLocale());
            
            return view('moondays.statenext', $this->data);
        }
        
        
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
        
    }
    
    public function category($lang = en, $id = 0) {
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        app()->setLocale($lang);
        
        $category = new Category();
        $state = new State();
        $mstate = new MoondayState();
        $moonday = new Moonday();
        $week = $this->data['calendar'];
        $year = date("Y");
        $month = date("m");
        
        $type = 1; // or $type = -1
        $current_month = date('m');
        $nextmonth = date('m', mktime(0, 0, 0, $current_month + $type, 1, date("Y")));
        
        $categori = $category->where('alias','LIKE',$id)->where('lang_id',  $langid)->where('status','=',1)->first();
        
        if($categori) {
            
            $catstat = $state->where('category_id','=',$categori->id)->where('lang_id',  $langid)->where('status','=',1)->get();
            $catstats = array();
            
            foreach($catstat as $stat) {
                
                $state_raw = array();
                foreach($stat->moondays as $day) {
                    
                    $ill = $day->pivot->toArray();
                    
                    $stateill = $mstate->where('state_id','=',$ill['state_id'])->where('moonday_id','=',$ill['moonday_id'])->where('lang_id',  $langid)->first()->toArray();
                    
                    $state_raw[$stat->alias][$day->pivot->moonday_id] = $stateill;
                    switch ($state_raw[$stat->alias][$day->pivot->moonday_id]['lucky']) {
                        case 1:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-horrible'])) ? $this->data['langtext']['state-horrible']: "Horrible";
                            break;
                        case 2:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-bad'])) ? $this->data['langtext']['state-bad']: "Bad";
                            break;
                        case 4:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-good'])) ? $this->data['langtext']['state-good']: "Good";
                            break;
                        case 5:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-great'])) ? $this->data['langtext']['state-great']: "Great";
                            break;
                        default:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-average'])) ? $this->data['langtext']['state-average']: "Average";
                    }
                }
                
                $catstats[] = $state_raw;
            }
            
            
            $newcatstat = array();
            foreach($catstat as $stat) {
                $newcatstat[$stat->alias] = $stat->h1;
            }
            $categori->title = str_replace('на', 'на %month% на', $categori->title);
            $categori->title = str_replace('для', 'на %month% для', $categori->title);
            
            $this->data['category'] = $categori;
            $this->data['catstats'] = $catstats;
            $this->data['week'] = $week;
            $this->data['monthtitle'] = $moonday->getMonth(0, app()->getLocale());
            $this->data['catstat'] = $newcatstat;
            $this->data['seo_title'] = $this->data['category']->seo_title;
            $this->data['seo_title'] = mb_strtoupper(mb_substr($this->data['seo_title'], 0, 1)).mb_substr($this->data['seo_title'], 1, mb_strlen($this->data['seo_title']));
            $this->data['seo_desc'] = $this->data['category']->seo_description;
            $this->data['seo_key'] = $this->data['category']->seo_keywords;
            $this->data['category_seo'] = $id;
            $this->data['unnextmonthtitle'] = $moonday->getMonth($nextmonth, app()->getLocale());
            
            
            $pagestest = $category->where('alias','LIKE',$id)->where('status','=',1)->get();
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
            
            
            return view('moondays.category', $this->data);
        }
        
        
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
        
    }
    
    public function categorynext($lang = en, $id = 0) {
        
        $uri = str_replace('categorynext', 'category', $_SERVER['REQUEST_URI']);
        header("Location: ".$uri, true, 301);
        exit();
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        app()->setLocale($lang);
        
        $category = new Category();
        $state = new State();
        $moonday = new Moonday();
        $mstate = new MoondayState();
        
        $type = 1; // or $type = -1
        $current_month = date('m');
        $nextmonth = date('m', mktime(0, 0, 0, $current_month + $type, 1, date("Y")));
        
        
        
        $week = $moonday->calendarnext();
        $year = date('Y', mktime(0, 0, 0, date('m') + 1, 1, date("Y")));
        $month = date('m', mktime(0, 0, 0, date('m') + 1, 1, date("Y")));
        
        $categori = $category->where('alias','LIKE',$id)->where('lang_id',  $langid)->first();
        
        if($categori) {
            
            $catstat = $state->where('category_id','=',$categori->id)->where('lang_id',  $langid)->where('status','=',1)->get();
            
            //print_r('<pre>'); print_r($catstat); exit();
            
            $catstats = array();
            
            foreach($catstat as $stat) {
                
                $state_raw = array();
                foreach($stat->moondays as $day) {
                    
                    $ill = $day->pivot->toArray();
                    
                    $stateill = $mstate->where('state_id','=',$ill['state_id'])->where('moonday_id','=',$ill['moonday_id'])->where('lang_id',  $langid)->first()->toArray();
                    
                    //print_r('<pre>'); print_r($stateill); exit();
                    
                    $state_raw[$stat->alias][$day->pivot->moonday_id] = $stateill;
                    switch ($state_raw[$stat->alias][$day->pivot->moonday_id]['lucky']) {
                        case 1:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-horrible'])) ? $this->data['langtext']['state-horrible']: "Horrible";
                            break;
                        case 2:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-bad'])) ? $this->data['langtext']['state-bad']: "Bad";
                            break;
                        case 4:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-good'])) ? $this->data['langtext']['state-good']: "Good";
                            break;
                        case 5:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-great'])) ? $this->data['langtext']['state-great']: "Great";
                            break;
                        default:
                            $state_raw[$stat->alias][$day->pivot->moonday_id]['lucky_title'] = (isset($this->data['langtext']['state-average'])) ? $this->data['langtext']['state-average']: "Average";
                    }
                }
                
                $catstats[] = $state_raw;
            }
            $newcatstat = array();
            foreach($catstat as $stat) {
                $newcatstat[$stat->alias] = $stat->h1;
            }
            $categori->title = str_replace('на', 'на %nextmonth% на', $categori->title);
            $categori->title = str_replace('для', 'на %nextmonth% для', $categori->title);
            
            $this->data['category'] = $categori;
            $this->data['catstats'] = $catstats;
            $this->data['week'] = $week;
            $this->data['monthtitle'] = $moonday->getMonth(0, app()->getLocale());
            $this->data['catstat'] = $newcatstat;
            
            $this->data['seo_title'] = str_replace('%month%', '%nextmonth%', $this->data['category']->seo_title);
            $this->data['seo_title'] = mb_strtoupper(mb_substr($this->data['seo_title'], 0, 1)).mb_substr($this->data['seo_title'], 1, mb_strlen($this->data['seo_title']));
            $this->data['seo_desc'] = $this->data['category']->seo_description;
            $this->data['seo_key'] = $this->data['category']->seo_keywords;
            
            
            $this->data['unnextmonthtitle'] = $moonday->getMonth($nextmonth, app()->getLocale());
            
            return view('moondays.categorynext', $this->data);
        }
        
        
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
        
    }
}
