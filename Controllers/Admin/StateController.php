<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use App\State;
use App\MoondayState;
use App\PageState;
use App\Category;
use App\Lang;

class StateController extends Controller
{
    public function index() {

        $state = new State;

        $data = [
            'states' => $state->where('lang_id', '1')->get(),
        ];

        return view('admin.states', $data);
    }

    public function update(State $state, Request $request, $id) {

        
        /*
         $categories = $state->where('lang_id', '1')->get()->toArray();
         
         foreach($categories as $vall) {
         
         $inputlangs = $vall;
         $inputlangs['lang_id'] = 4;
         
         
         $categorysave = new State;
         $categorysave->fill($inputlangs);
         
         
         $categorysave->save();
         }
         */
        
        $langs = new Lang();
        $langlists =  $langs->all()->toArray();
        
        
        $moondaystate =  new MoondayState();
        $state1 = $state->where('id','=',$id)->get();
        
        foreach($state1->toArray() as $val) {
            $old[$val['lang_id']] = $val;
            
            $moondaystates =  $moondaystate->where('state_id','=',$val['id'])->where('lang_id','=',$val['lang_id'])->get();
            
            
            $i = 0;
            foreach($moondaystates as $vall) {
                $i++;
                $old[$val['lang_id']]['state'][$vall['moonday_id']] = $vall;
            }
            if($i == 0) {
                $moondaystates =  $moondaystate->where('state_id','=',$val['id'])->where('lang_id','=',1)->get();
                foreach($moondaystates as $vall) {
                    $old[$val['lang_id']]['state'][$vall['moonday_id']] = $vall;
                }
            }
        }

        
        if(count($old) != count($langlists)) {
            foreach($langlists as $val) {
                if(!isset($old[$val['id']])) {
                    $old[$val['id']] = $old[1];
                    $inputlangs = (array)$old[1];
                    $inputlangs['lang_id'] = $val['id'];
                    $category = new State;
                    $category->fill($inputlangs);
                    $category->save();
                }
            }
        }
        
        
        

        if($request->isMethod('delete')) {
            $moondaystates = new MoondayState();
            $moondaystates->where('state_id',$id)->delete();
            $moondaystates = new PageState();
            $moondaystates->where('state_id',$id)->delete();
            $state->where('id', $id)->delete();
            
            return redirect()->route('admin_states_index')->with('status', 'Страница удалена');
        }

        if($request->isMethod('post')) {
            $input = $request->except('_token');
            //$validator = Validator::make($input,[
            //    'alias' => 'required|unique:states,alias,'.$id
            //]);

            //if($validator->fails()) {
            //    return redirect()->route('admin_states_edit',['id' => $id])->withErrors($validator)->withInput();
            //}

            
            $inputlangs = array();
            foreach($langlists as $val) {
                
                $inputlangs['id'] = $id;
                $inputlangs['lang_id'] = $val['id'];
                
                $inputlangs['alias'] = $input['alias'];
                $inputlangs['status'] = $input['status'][$val['id']];
                $inputlangs['title'] = $input['title'][$val['id']];
                $inputlangs['h1'] = $input['h1'][$val['id']];
                $inputlangs['nexth1'] = $input['nexth1'][$val['id']];
                $inputlangs['description'] = $input['description'][$val['id']];
                $inputlangs['seo_title'] = $input['seo_title'][$val['id']];
                $inputlangs['seo_description'] = $input['seo_description'][$val['id']];
                $inputlangs['nextseo_title'] = $input['nextseo_title'][$val['id']];
                $inputlangs['nextseo_description'] = $input['nextseo_description'][$val['id']];
                
                $inputlangs['for_text_nominative'] = $input['for_text_nominative'][$val['id']];
                $inputlangs['for_text_genitive'] = $input['for_text_genitive'][$val['id']];
                
                $inputlangs['on_main'] = $input['on_main'];
                $inputlangs['category_id'] = $input['category_id'];
                
            
                $uploadfile = LARAVEL_PATH.'/images/imagestate/';
                $name = explode('.', $_FILES['image']['name']);
                $name = end($name);
                $name = $id.'.'.$name;
                $uploadfile .= $name;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                    $inputlangs['image'] = '/images/imagestate/'.$name;
                } else {
                    $inputlangs['image'] = $old[1]['image'];
                }
                
                $statesave = $state->where('id','=',$id)->where('lang_id','=',$val['id'])->first();
                if($statesave) {
                    $statesave->fill($inputlangs);
                    if(!isset($input['on_main'])) $statesave->on_main = 0;
                    if(!isset($input['month_1'])) $statesave->month_1 = 0;
                    if(!isset($input['month_2'])) $statesave->month_2 = 0;
                    if(!isset($input['month_3'])) $statesave->month_3 = 0;
                    if(!isset($input['month_4'])) $statesave->month_4 = 0;
                    if(!isset($input['month_5'])) $statesave->month_5 = 0;
                    if(!isset($input['month_6'])) $statesave->month_6 = 0;
                    if(!isset($input['month_7'])) $statesave->month_7 = 0;
                    if(!isset($input['month_8'])) $statesave->month_8 = 0;
                    if(!isset($input['month_9'])) $statesave->month_9 = 0;
                    if(!isset($input['month_10'])) $statesave->month_10 = 0;
                    if(!isset($input['month_11'])) $statesave->month_11 = 0;
                    if(!isset($input['month_12'])) $statesave->month_12 = 0;
                    
                    $statesave->update();
                } else {
                    $statesave->fill($inputlangs);
                    if(!isset($input['on_main'])) $statesave->on_main = 0;
                    if(!isset($input['month_1'])) $statesave->month_1 = 0;
                    if(!isset($input['month_2'])) $statesave->month_2 = 0;
                    if(!isset($input['month_3'])) $statesave->month_3 = 0;
                    if(!isset($input['month_4'])) $statesave->month_4 = 0;
                    if(!isset($input['month_5'])) $statesave->month_5 = 0;
                    if(!isset($input['month_6'])) $statesave->month_6 = 0;
                    if(!isset($input['month_7'])) $statesave->month_7 = 0;
                    if(!isset($input['month_8'])) $statesave->month_8 = 0;
                    if(!isset($input['month_9'])) $statesave->month_9 = 0;
                    if(!isset($input['month_10'])) $statesave->month_10 = 0;
                    if(!isset($input['month_11'])) $statesave->month_11 = 0;
                    if(!isset($input['month_12'])) $statesave->month_12 = 0;
                    
                    $statesave->save();
                }
                
                
                foreach($input['state'][$val['id']] as $vall) {
                    $vall['state_id'] = $id;
                    $vall['lang_id'] = $val['id'];
                    
                    $moondaystates = new MoondayState();
                    $moondaystate = $moondaystates->where('id','=',$vall['id'])->where('lang_id','=',$val['id'])->first();
                    if(!$moondaystate) {
                        $moondaystate1 = new MoondayState();
                        $moondaystate1->fill($vall)->save();
                    } else {
                        $moondaystate->fill($vall)->update();
                    }
                    
                }
            }
            
            return redirect()->route('admin_states_edit',['id' => $id])->with('status', 'Страница изменена');
        }


        if(view()->exists('admin.editState')) {
            $category = new Category;
            $data = [
                'title' => 'Новая категория подходящих дней - '.$old[1]['title'],
                'data' => $old,
                'categories' => $category->where('lang_id', '1')->get(),
                'langlists' => $langlists
            ];
            return view('admin.editState', $data);
        }
        abort(404);
    }

    public function create(Request $request) {

        $langs = new Lang();
        $langlists =  $langs->all()->toArray();
        
        
        if($request->isMethod('post')) {
            $input = $request->except('_token');
            //$validator = Validator::make($input,[
                //'alias' => 'required|unique:states'
            //]);

            //if($validator->fails()) {
            //    return redirect()->route('admin_states_add')->withErrors($validator)->withInput();
            //}

            $inputlangs = array();
            
            foreach($langlists as $val) {
            
                
                $inputlangs['lang_id'] = $val['id'];
                
                $inputlangs['alias'] = $input['alias'];
                $inputlangs['status'] = $input['status'][$val['id']];
                $inputlangs['title'] = $input['title'][$val['id']];
                $inputlangs['h1'] = $input['h1'][$val['id']];
                $inputlangs['nexth1'] = $input['nexth1'][$val['id']];
                $inputlangs['description'] = $input['description'][$val['id']];
                $inputlangs['seo_title'] = $input['seo_title'][$val['id']];
                $inputlangs['seo_description'] = $input['seo_description'][$val['id']];
                $inputlangs['nextseo_title'] = $input['nextseo_title'][$val['id']];
                $inputlangs['nextseo_description'] = $input['nextseo_description'][$val['id']];
                
                $inputlangs['for_text_nominative'] = $input['for_text_nominative'][$val['id']];
                $inputlangs['for_text_genitive'] = $input['for_text_genitive'][$val['id']];
                
                $inputlangs['on_main'] = $input['on_main'];
                $inputlangs['category_id'] = $input['category_id'];
            
                $state = new State;
                $state->fill($inputlangs);
                if(!isset($input['on_main'])) $state->on_main = 0;
                if(!isset($input['month_1'])) $state->month_1 = 0;
                if(!isset($input['month_2'])) $state->month_2 = 0;
                if(!isset($input['month_3'])) $state->month_3 = 0;
                if(!isset($input['month_4'])) $state->month_4 = 0;
                if(!isset($input['month_5'])) $state->month_5 = 0;
                if(!isset($input['month_6'])) $state->month_6 = 0;
                if(!isset($input['month_7'])) $state->month_7 = 0;
                if(!isset($input['month_8'])) $state->month_8 = 0;
                if(!isset($input['month_9'])) $state->month_9 = 0;
                if(!isset($input['month_10'])) $state->month_10 = 0;
                if(!isset($input['month_11'])) $state->month_11 = 0;
                if(!isset($input['month_12'])) $state->month_12 = 0;

                
                if($request->hasFile('image')) {
                    $file = $request->file('image');
                    $file->move(LARAVEL_PATH.'/images/imgs', $file->getClientOriginalName());
                    $state->image = $file->getClientOriginalName();
                } else {
                    $state->image = '';
                }
                
                if($state->save()) {
                    
                    $cat = $state->where('alias','LIKE', $input['alias'])->first()->toArray();
                    
                    if(isset($cat['id'])) {
                        $inputlangs['id'] = $cat['id'];
                    }
                    
                    foreach($input['state'][$val['id']] as $vall) {
                        $moondaystate = new MoondayState();
                        $vall['state_id'] = $inputlangs['id'];
                        $vall['lang_id'] = $val['id'];
                        $moondaystate->fill($vall)->save();
                    }
                }
                
                
                
            }
            return redirect()->route('admin_states_index')->with('status', 'Страница добавлена');
        }

        if(view()->exists('admin.editState')) {
            $category = new Category;

            $data = [
                'title' => 'Новый подходящий день',
                'categories' => $category->where('lang_id', '1')->get(),
                'langlists' => $langlists
            ];
            return view('admin.addState', $data);
        }

        abort(404);
    }
}
