<?php

namespace App\Http\Controllers\Admin;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Region;
use App\City;
use App\Lang;

class CityController extends Controller
{
    //

    public function index() {

        $city = new City;
        
        $data = [
            'cities' => $city->where('lang_id', '1')->get()
        ];
        
        
        
        return view('admin.cities', $data);
    }

    public function update(City $city, Request $request, $id) {

        
        $langs = new Lang();
        $langlists =  $langs->all()->toArray();
        
        
        $city = $city->where('id','=',$id)->get();

        foreach($city->toArray() as $val) {
            $old[$val['lang_id']] = $val;
        }
        
        if(count($old) != count($langlists)) {
            foreach($langlists as $val) {
                if(!isset($old[$val['id']])) {
                    $old[$val['id']] = $old[1];
                    $inputlangs = (array)$old[1];
                    $inputlangs['lang_id'] = $val['id'];
                    $category = new City;
                    $category->fill($inputlangs);
                    $category->save();
                }
            }
        }
        
        if($request->isMethod('delete')) {
            foreach($langlists as $val) {
                $city = $city->where('lang_id', $val['id'])->where('id', $id)->first();
                $city->delete();
            }
            return redirect()->route('admin_cities_index')->with('status', 'Город удален');
        }

        if($request->isMethod('post')) {
            $input = $request->except('_token');
            /*$validator = Validator::make($input,[
                'alias' => 'required|unique:cities,alias,'.$id,
                'region_id' => 'required|exists:regions,id',
                'latitude' => 'required',
                'lontitude' => 'required'
            ]);*/
            $inputlangs = array();
            
            foreach($langlists as $val) {
                
                $inputlangs['alias'] = $input['alias'];
                $inputlangs['region_id'] = $input['region_id'];
                $inputlangs['latitude'] = $input['latitude'];
                $inputlangs['lontitude'] = $input['lontitude'];
                
                $inputlangs['lang_id'] = $val['id'];
                
                $inputlangs['status'] = $input['status'][$val['id']];
                $inputlangs['title'] = $input['title'][$val['id']];
                $inputlangs['padej'] = $input['padej'][$val['id']];
                $inputlangs['description'] = $input['description'][$val['id']];
                $inputlangs['seo_title'] = $input['seo_title'][$val['id']];
                $inputlangs['seo_description'] = $input['seo_description'][$val['id']];
                $inputlangs['h1'] = $input['h1'][$val['id']];
            
                if($request->hasFile('image')) {
                    $file = $request->file('image');
                    $file->move(public_path().'/images/imgs', $file->getClientOriginalName());
                    $inputlangs['image'] = $file->getClientOriginalName();
                } elseif(isset($input['old_image'])) {
                    $inputlangs['image'] = $input['old_image'];
                }
                
                $city = $city->where('id','=',$id)->where('lang_id','=',$val['id'])->first();
                $city->fill($inputlangs);
                $city->update();
            
            //die('required|unique:cities,alias,'.$id);
/*
            if($validator->fails()) {
                return redirect()->route('admin_cities_edit',['id' => $id])->withErrors($validator)->withInput();
            }
*/
            }
            return redirect()->route('admin_cities_edit',['id' => $id])->with('status', 'Город изменен');

        }


        if(view()->exists('admin.editCity')) {
            $country = new Region;
            $data = [
                'title' => 'Редактирование города - '.$old[1]['title'],
                'data' => $old,
                'countries' => $country->all(),
                'langlists' => $langlists
            ];
            return view('admin.editCity', $data);
        }
        abort(404);
    }

    public function create(Request $request) {
        
        $langs = new Lang();
        $langlists =  $langs->all()->toArray();
        
        if($request->isMethod('post')) {
            $input = $request->except('_token');
/*
            $validator = Validator::make($input,[
                'alias' => 'required|unique:cities',
                'region_id' => 'required|exists:regions,id',
                'latitude' => 'required',
                'lontitude' => 'required'
            ]);

            if($validator->fails()) {
                return redirect()->route('admin_cities_add')->withErrors($validator)->withInput();
            }
*/
            
            if($request->hasFile('image')) {
                $file = $request->file('image');
                $file->move(public_path().'/images/imgs', $file->getClientOriginalName());
                $input['image'] = $file->getClientOriginalName();
            }
            
            
            $inputlangs = array();
            
            foreach($langlists as $val) {
                
                $inputlangs['alias'] = $input['alias'];
                $inputlangs['region_id'] = $input['region_id'];
                $inputlangs['latitude'] = $input['latitude'];
                $inputlangs['lontitude'] = $input['lontitude'];
                
                $inputlangs['lang_id'] = $val['id'];
                
                $inputlangs['status'] = $input['status'][$val['id']];
                $inputlangs['title'] = $input['title'][$val['id']];
                $inputlangs['padej'] = $input['padej'][$val['id']];
                $inputlangs['description'] = $input['description'][$val['id']];
                $inputlangs['seo_title'] = $input['seo_title'][$val['id']];
                $inputlangs['seo_description'] = $input['seo_description'][$val['id']];
                $inputlangs['h1'] = $input['h1'][$val['id']];
                $inputlangs['image'] = $input['image'];
                
                $city = new City;
                $city->fill($inputlangs);
                $city->save();
                
                //die('required|unique:cities,alias,'.$id);
                /*
                 if($validator->fails()) {
                 return redirect()->route('admin_cities_edit',['id' => $id])->withErrors($validator)->withInput();
                 }
                 */
                $cat = $city->where('alias','LIKE', $input['alias'])->first()->toArray();
                
                if(isset($cat['id'])) {
                    $inputlangs['id'] = $cat['id'];
                }
            }
            
            return redirect()->route('admin_cities_index')->with('status', 'Город добавлена');
        }

        if(view()->exists('admin.addCity')) {
            $region = new Region;
            $data = [
                'title' => 'Новый город',
                'countries' => $region->all(),
                'langlists' => $langlists
            ];
            return view('admin.addCity', $data);
        }

        abort(404);
    }
}
