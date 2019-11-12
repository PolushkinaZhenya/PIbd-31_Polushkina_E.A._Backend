<?php

namespace App\Http\Controllers;

use App\Vacancy;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
class DropboxController extends Controller
{
    public function uploadToDropboxFile(Request $request)
    {
        $files = $request->allFiles();
        try {
            $id = array_key_first($files);
            $doc = Vacancy::find($id);
            for ($i = 0; $i < count($files)-2; $i++) {
                $url = Storage::disk('dropbox')->put($id.'_image_'.$i, $files["images_files_".$i]);
                if (array_key_exists('add', $files)) {
                    $doc->images()->create([
                        'original' => '/'.$url,
                    ]);
                }
                else {
                    $doc->images()->update([
                        'original' => '/'.$url,
                    ]);
                }
            }
            $status = '201';
            $list = $doc;
        }
        catch(Exception $e){
            $status = '422';
            $list = $e->getMessage();
        }
        $data = compact('list', 'status');
        return response()->json($data);
    }
}
