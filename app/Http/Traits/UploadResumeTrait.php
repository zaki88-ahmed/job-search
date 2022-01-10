<?php


namespace App\Http\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait UploadResumeTrait{


//    public function ApiResponse($code = 200, $message = null, $errors = null, $data = null){
//
//        $array = [
//            'status' => $code,
//            'message' => $message,
//        ];
//
//        if (is_null($data) && !is_null($errors)) {
//            $array['errors'] = $errors;
//        }
//        elseif (is_null($errors) && !is_null($data)) {
//            $array['data'] = $data;
//        }
//
//
//        return response($array, 200);
//
//    }


//    public function uploadImage($request){
//        dd($file);
//        $validator = Validator::make($request->all(), [
//           'file' => 'required|mimes:doc,docx,pdf,txt,csv,png,jpg,jpeg|max:2048',
//        ]);
//        if($validator->fails()){
//            return response()->json(['errors' => $validator->errors()]);
//        }

//        if($file = $request->file('file')){
//            $destinationPath = 'uploads';
//            $file->move($destinationPath, $file->getClientOriginalName());
//            return response()->json([
//                "success" => true,
//                "message" => 'FileUploaded Successfully',
//                "file" => $file
//            ]);
//            return $file;

//        }

//            $destinationPath = 'uploads';
//            $request->move($destinationPath, $request->getClientOriginalName());
//            return response()->json([
//               "success" => true,
//               "message" => 'FileUploaded Successfully',
//               "file" => $request
//            ]);
//    }


    public function uploadResume(Request $request, $path){
//        $validator = Validator::make($request->all(), [
//           'image' => 'required|mimes:png,jpg,jpeg|max:2048',
//        ]);
//        if($validator->fails()){
//            return response()->json(['errors' => $validator->errors()]);
//        }

        if($image = $request->file('resume')){
            $image = $image->store($path);
        }
        return $request->file('resume')->hashName();
    }

    public function deleteResume($path){
        unlink(storage_path('app/public/'.$path));
    }



}
