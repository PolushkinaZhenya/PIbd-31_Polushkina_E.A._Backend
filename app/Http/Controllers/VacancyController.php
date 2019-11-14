<?php

namespace App\Http\Controllers;

use App\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use WebSocket\Client;

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

            $status = '201';
            $list = $vacancy->id;
        } else {
            $status = '422';
            $list = $validator->errors();
        }
        $data = compact('list', 'status');

        $message = json_encode((string)$vacancy);
        $client = new Client('ws://labourexchangewebsocket.herokuapp.com/');
        $client->send($message);

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

        $vacancy = compact('id');

        $message = json_encode((string)$id);
        $client = new Client('ws://labourexchangewebsocket.herokuapp.com/');
        $client->send($message);

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

    public function searchby(Request $request)
    {
        try {
            $text = mb_strtolower($request->input('text'), 'UTF-8');
            $text = "'$text'";
            $query = "SELECT * FROM vacancies WHERE position LIKE CONCAT('%',".$text.",'%') OR description LIKE CONCAT('%',".$text.",'%');";
            $vacancies = DB::select($query);

            $status = '200';
            $list = $vacancies;
        } catch (Exception $e) {
            $status = '422';
            $list = $e->getMessage();
        }

        $data = compact('list', 'status');
        return response()->json($data);
    }

    public function vkCallBack(Request $request){
        if(($request->input('type') == 'confirmation') && ($request->input('group_id') == '152839114 ')){
            return response()->json('51895730');
        }
    }
}
