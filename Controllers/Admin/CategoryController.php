<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;
use App\State;
use App\MoondayState;
use App\Category;
use App\Lang;

class CategoryController extends Controller
{
    public function inner_cats($cat_id) {
        $category = new Category;

        $inner_cats = $category->where('parent_id', '=', $cat_id)->where('lang_id', '1')->get()->toArray();

        if(count($inner_cats) > 0) {
            foreach($inner_cats as $key => $val) {
                $inner_cats[$key]['children'] = $this->inner_cats($val['id']);
            }
        }

        return $inner_cats;
    }

    public function index() {

        $category = new Category;

        $categories = $category->where('parent_id', '0')->where('lang_id', '1')->orwhereNull('parent_id')->get()->toArray();

        foreach($categories as $key => $val) {
            $categories[$key]['children'] = $this->inner_cats($val['id']);
        }

        $data = [
            'categories' => $categories
        ];

        
        return view('admin.categories', $data);
    }


    public function create(Request $request) {
        
        $langs = new Lang();
        $langlists =  $langs->all()->toArray();
        
        $category = new Category;

        $categories = $category->where('parent_id', '0')->where('lang_id', '1')->orwhereNull('parent_id')->get()->toArray();

        foreach($categories as $key => $val) {
            $categories[$key]['children'] = $this->inner_cats($val['id']);
        }

        if($request->isMethod('post')) {
            $input = $request->except('_token');
            
            $vall = array('alias' => $input['alias']);
            
            //$validator = Validator::make($vall,[
                //'alias' => 'required|unique:pages'
            //]);
            
            //if($validator->fails()) {
                //return redirect()->route('admin_categories_add')->withErrors($validator); //->withInput();
            //}

            
            
            $inputlangs = array();
            
            foreach($langlists as $val) {
                
                $alias = $input['alias'];
                
                
                $inputlangs['lang_id'] = $val['id'];
                
                $inputlangs['status'] = $input['status'][$val['id']];
                $inputlangs['parent_id'] = $input['parent_id'];
                $inputlangs['alias'] = $alias;
                $inputlangs['on_main'] = $input['on_main'];
                 
                $inputlangs['title'] = $input['title'][$val['id']];
                $inputlangs['description'] = $input['description'][$val['id']];
                $inputlangs['seo_title'] = $input['seo_title'][$val['id']];
                $inputlangs['seo_description'] = $input['seo_description'][$val['id']];
                
                
                
                $category = new Category;
                $category->fill($inputlangs);
            
                if(!isset($input['on_main'])) $category->on_main = 0;
    
                $uploadfile = LARAVEL_PATH.'/images/image/';
                $name = explode('.', $_FILES['image']['name']);
                $name = end($name);
                $name = time().'.'.$name;
                $uploadfile .= $name;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                    $category->image = '/images/image/'.$name;
                } else {
                    $category->image = '';
                }
                
                $category->save();
                
                
                $cat = $category->where('alias','LIKE', $alias)->first()->toArray();
                
                if(isset($cat['id'])) {
                    $inputlangs['id'] = $cat['id'];
                }
            }
            
            return redirect()->route('admin_categories_index')->with('status', 'Страница добавлена');
        }

        if(view()->exists('admin.addCategory')) {
            $data = [
                'title' => 'Новая категория подходящих дней',
                'categories' => $categories,
                'langlists' => $langlists
            ];
            return view('admin.addCategory', $data);
        }

        abort(404);
    }

    public function update(Category $category, Request $request, $id) {

        $langs = new Lang();
        $langlists =  $langs->all()->toArray();
        
        $category1 = $category->where('id','=',$id)->get();
        
        if($request->isMethod('delete')) {
            @unlink(LARAVEL_PATH.$category->image);
            
            $category1 = $category->where('id','=',$id)->first();
            
            if($category1->parent_id != 0) {
                $state = new State;
                $moondaystate = $state->where('category_id', $id)->get();
                foreach($moondaystate->toArray() as $val) {
                    $val['category_id'] = $category1->parent_id;
                    $statenew = $state->where('id', $val['id'])->where('lang_id', $val['lang_id'])->first();
                    $statenew->fill($val)->update();
                }
                
                $category->where('id', $id)->delete();
                
                
                return redirect()->route('admin_categories_index')->with('status', 'Страница удалена');
            } else {
                $state = new State;
                $moondaystate = $state->where('category_id', $id)->where('lang_id', 1)->get();
                $ids = array();
                foreach($moondaystate->toArray() as $val) {
                    $ids[] = $val['id'];
                }
                return redirect('/admin/categories/edit/'.$id)->with('status', 'Подвязанные подходящие дни id = '.implode(',', $ids));
            }
            
            
        }
        
        
        foreach($category1->toArray() as $val) {
            $old[$val['lang_id']] = $val;
        }
        
        if(count($old) != count($langlists)) {
            foreach($langlists as $val) {
                if(!isset($old[$val['id']])) {
                    $old[$val['id']] = $old[1];
                    $inputlangs = (array)$old[1];
                    $inputlangs['lang_id'] = $val['id'];
                    $category = new Category;
                    $category->fill($inputlangs);
                    $category->save();
                }
            }
        }
        
        //print_r('<pre>'); print_r($langlists); exit();

        $categories = $category->where('parent_id', '0')->where('lang_id', '1')->orwhereNull('parent_id')->get()->toArray();

        foreach($categories as $key => $val) {
            $categories[$key]['children'] = $this->inner_cats($val['id']);
        }


        //$old = $category->toArray();
        
        

        if($request->isMethod('post')) {
            
            /*
            $categories = $category->where('lang_id', '1')->where('id','!=', '1')->get()->toArray();
            
            foreach($categories as $vall) {
                
                $inputlangs['id'] = $vall['id'];
                $inputlangs['lang_id'] = 2;
                
                $inputlangs['parent_id'] = $vall['parent_id'];
                $inputlangs['alias'] = $vall['alias'];
                $inputlangs['on_main'] = $vall['on_main'];
                
                $inputlangs['title'] = $vall['title'];
                $inputlangs['description'] = $vall['description'];
                $inputlangs['seo_title'] = $vall['seo_title'];
                $inputlangs['seo_description'] = $vall['seo_description'];
                
                
                $categorysave = new Category;
                $categorysave->fill($inputlangs);
                
                
                $categorysave->save();
            }
            */
            
            
            
            $input = $request->except('_token');
            $validator = Validator::make($input,[
                'title' => 'required',
                //'alias' => 'required|unique:categories,alias,'.$id
            ]);

            if($validator->fails()) {
                return redirect()->route('admin_categories_edit',['id' => $id])->withErrors($validator); //->withInput();
            }

            $inputlangs = array();
            
            foreach($langlists as $val) {
                
                $alias = $input['alias']; //.'-'. $val['alias'];
                
                $inputlangs['id'] = $id;
                $inputlangs['lang_id'] = $val['id'];
                $inputlangs['status'] = $input['status'][$val['id']];
                $inputlangs['parent_id'] = $input['parent_id'];
                $inputlangs['alias'] = $alias;
                $inputlangs['on_main'] = $input['on_main'];
                
                $inputlangs['title'] = $input['title'][$val['id']];
                $inputlangs['description'] = $input['description'][$val['id']];
                $inputlangs['seo_title'] = $input['seo_title'][$val['id']];
                $inputlangs['seo_description'] = $input['seo_description'][$val['id']];
                
                
                $categorysave = $category->where('id','=',$id)->where('lang_id','=',$val['id'])->first();
                if($categorysave) {
                    $categorysave->fill($inputlangs);
                    
                    if(!isset($input['on_main'])) $categorysave->on_main = 0;
                    
                    $uploadfile = LARAVEL_PATH.'/images/image/';
                    $name = explode('.', $_FILES['image']['name']);
                    $name = end($name);
                    $name = $id.'.'.$name;
                    $uploadfile .= $name;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                        $categorysave->image = '/images/image/'.$name;
                    } else {
                        $categorysave->image = $old[1]['image'];
                    }
                    if($categorysave->image != $old[1]['image']) {
                        @unlink(LARAVEL_PATH.$old[1]['image']);
                    }
                    
                    $categorysave->update();
                } else {
                    $categorysave = new Category;
                    $categorysave->fill($inputlangs);
                    
                    if(!isset($input['on_main'])) $categorysave->on_main = 0;
                    
                    $uploadfile = LARAVEL_PATH.'/images/image/';
                    $name = explode('.', $_FILES['image']['name']);
                    $name = end($name);
                    $name = $id.'.'.$name;
                    $uploadfile .= $name;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                        $categorysave->image = '/images/image/'.$name;
                    } else {
                        $categorysave->image = $old[1]['image'];
                    }
                    
                    $categorysave->save();
                }
                
                
            }
            
            return redirect()->route('admin_categories_edit',['id' => $id])->with('status', 'Страница изменена');
        }


        if(view()->exists('admin.editCategory')) {
            $data = [
                'title' => 'Категория подходящих дней - '.$old[1]['title'],
                'data' => $old,
                'categories' => $categories,
                'langlists' => $langlists
            ];
            return view('admin.editCategory', $data);
        }
        abort(404);
    }

}
