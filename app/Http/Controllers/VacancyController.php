<?php

namespace App\Http\Controllers;

use App\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VacancyController extends Controller
{
/* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
    public function index()
    {
        $list = Vacancy::with('images')->get();
        $status = '200';
        $data = compact('list', 'status');

        return response()->json($data);
    }

/* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required',
            'description' => 'required',
            'salary' => 'required',
            //'images' => 'required',
            // 'images.*' => 'mimes:png,gif,jpeg',
        ], [
            'required' => 'Обязательное поле',
        ]);
        if($validator->passes()) {
            //сохранять в бд
            $vacancy = Vacancy::create([
                'position' => $request->input('position'),
                'description' => $request->input('description'),
                'salary' => $request->input('salary')
            ]);

//           $images = $request->input('images');
//         for($i = 0; $i < count($images); ++$i) {
//            $vacancy->images()->create([
//                'original' => $images[$i]["original"]
//             ]);
//         }

            $status = '201';
            $list = $vacancy->id;
        } else {
            $status = '422';
            $list = $validator->errors();
        }
        $data = compact('list', 'status');

        return response()->json($data);
    }

/* Display the specified resource.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
    public function show($id)
    {
        $vacancy = Vacancy::with('images')->find($id);
        $list = $vacancy;
        $status = $vacancy ? '200' : '404';
        $data = compact('list','status');

        return response()->json($data);
    }

/* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  int  $id
* @return \Illuminate\Http\Response
*/
    public function update(Request $request, $id)
    {
        $vacancy = Vacancy::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'position' => 'required',
            'description' => 'required',
            'salary' => 'required',
            //'images' => 'required',
            // 'images.*' => 'mimes:png,gif,jpeg',
        ], [
            'required' => 'Обязательное поле',
        ]);

        if($validator->passes()) {
            //сохранять в бд
            $vacancy->update([
                'position' => $request->input('position'),
                'description' => $request->input('description'),
                'salary' => $request->input('salary')
            ]);

//           $images = $request->input('images');
//          for($i = 0; $i < count($images); ++$i) {
//              $vacancy->images()->update([
//                  'original' => $images[$i]["original"]
//               ]);
//           }

            $status = '201';
        } else {
            $status = '422';
            $vacancy = $validator->errors();
        }
        $data = compact('vacancy','status');

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $vacancy = Vacancy::findOrFail($id);
        $images = $vacancy->images()->pluck('original');
        foreach ($images as $image){
            Storage::disk('dropbox')->delete(substr($image, 1));
            Storage::disk('dropbox')->deleteDirectory(explode('/',trim(substr($image, 1)))[0]);
        }
        $vacancy->images()->delete();
        $vacancy->delete($id);
        $status = '204';

        return response()->json($status);
    }
    public function search(Request $request)
    {
        try
        {
            $text = mb_strtolower($request->input('text'), 'UTF-8');
            $text = "'$text'";
            $query = "CALL search(" . $text . ");";
            $vacancies = DB::select($query);

            $status = '200';
            $list = $vacancies;
        }
        catch(Exception $e){
            $status = '422';
            $list = $e->getMessage();
        }

        $data = compact('list', 'status');
        return response()->json($data);
    }
}
