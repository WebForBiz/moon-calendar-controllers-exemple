<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Moonphase;

class MoonphaseController extends BaseController
{
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function show($lang = en, $id = 0) {
        
        if($id == '0') {
            $id = $lang;
            $lang = 'en';
        }
        
        $langid = $this->data['langid'];
        
        $moonphase = new Moonphase();

        $this->data['moonphase'] = $moonphase->where('alias','LIKE',$id)->where('lang_id',  $langid)->first();
        
        if($this->data['moonphase']) {
        
            $this->data['seo_title'] = $this->data['moonphase']->seo_title;
            $this->data['seo_desc'] = $this->data['moonphase']->seo_description;
            $this->data['seo_key'] = $this->data['moonphase']->seo_keywords;
    
            
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
            
            return view('moonphase.show', $this->data);
        }
        
        
        $data['title'] = '404';
        $data['name'] = 'Page not found';
        return response()->view('errors.404',$this->data,404);
    }
}
