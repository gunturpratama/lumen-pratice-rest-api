<?php

namespace App\Http\Controllers;
use App\Fleets;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class FleetsController extends Controller
{
    public function index(Request $request){
        $fleets = Fleets::orderBy('created_at','DESC')->when($request->q,function($fleets) use($request) {
            $fleets->where('plate_number',$request->plate_number);
        })->paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => $fleets
        ]);
    }

    public function store(Request $request){

        $this->validate($request,[
            'plate_numbers' => 'required|unique:fleets,plate_numbers|string',
            'type' => 'required',
            'photo' => 'required|image|mimes:png,jpg,jpeg'
        ]);

        $user = $request->user();
        $file = $request->file('photo');
        $fileName = $request->plate_numbers . '-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move('Fleets',$fileName);
        Fleets::create([
            'plate_numbers' => $request->plate_numbers,
            'type' => $request->type,
            'photo' => $fileName,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'status' => 'success'
        ]);

    }

    public function edit($id) {
        $fleets = Fleets::find($id);
        return response()->json([
            'status' => 'success',
            'data' => $fleets,
        ]);
    }


    public function update(Request $request,$id){

        $this->validate($request,[
            'plate_numbers' => 'required|string|unique:fleets,plate_numbers',$id,
            'type' => 'required',
            'photo' => 'nullable'
        ]);

        $fleets = Fleets::find($id);
        $fileName = $fleets->photo; // old file

        if ($request->hasFile('photo')) {
            # code...
            $file = $request->file('photo');
            $fileName = $request->plate_numbers . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('Fleets',$fileName);
            File::delete(base_path('public/fleets/'.$fleets->photo));
        }

        $fleets->update([
            'plate_numbers' => $request->plate_numbers,
            'type' => $request->type,
            'photo' => $fileName,
            // 'user_id' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $fleets
        ]);


    }

    public function destroy($id){
        $fleets = Fleets::find($id);
        File::delete(base_path('public/fleets/'.$fleets->photo));
        $fleets->delete();

        return response()->json([
            'status' => 'Success',
        ]);
    }
}
