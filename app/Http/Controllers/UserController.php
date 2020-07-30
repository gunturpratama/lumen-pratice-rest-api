<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function index(Request $request){
        $users = User::orderBy('created_at', 'desc')->when($request->q.function($users) use($request) {
            $users = $users->where('name','LIKE','%'.$request->q.'%');
        });
        return response()->json([
            'status' => 'Success',
            'data' => $users
        ]);
    }

    public function store(Request $request){

        $this->validate($request, [
            'name' => 'required|string|max:50',
            'identity_id' => 'required|string|unique:users',
            'gender' => 'required',
            'address' => 'required|string',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg',
            'email' => 'required|string|unique:users',
            'password' => 'required|min:6',
            'phone_number' => 'required|string',
            'role' => 'required',
            'status' => 'required',
        ]);

        $filename = null;
        if ($request->hasFile('photo')) {
            $filename = Str::random(10). $request->email . '.jpg';
            $file = $request->file('photo');
            $file->move(base_path('public/image'),$filename);
        }

        User::create([
            'name' => $request->name,
            'identity_id' => $request->identity_id,
            'gender' => $request->gender,
            'address' => $request->address,
            'photo' => $filename,
            'email' => $request->email,
            'password' => app('hash')->make($request->password),
            'phone_number' => $request->phone_number,
            // 'api_token' => $request->role,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return response()->json(['status' => 'Success' ]);
    }

    public function edit($id){

        $user = User::find($id);

        return response()->json(['status' => 'Success' , 'data' => $user]);
    }

    public function update(Request $request,$id){


        $this->validate($request, [
            'name' => 'required|string|max:50',
            'identity_id' => 'required|string|unique:users,identity_id,'. $id,
            'gender' => 'required',
            'address' => 'required|string',
            'photo' => 'nullable|image|mimes:jpg,png,jpeg',
            'email' => 'required|string|unique:users,email,'.$id,
            'password' => 'required|min:6',
            'phone_number' => 'required|string',
            'role' => 'required',
            'status' => 'required',
        ]);

        $user = User::find($id);
        $password = $request->password != '' ? app('hash')->make($request->password) : $user->password;
        $filename = $user->photo;
        if ($request->hasFile('photo')) {
            # code...
            $filename = Str::random(10). $user->email . '.jpg';
            $file = $request->file('photo');
            $file->move(base_path('public/image'),$filename);
            unlink(base_path('public/image/'. $user->photo));
        }

        $user->update([
            'name' => $request->name,
            'identity_id' => $request->identity_id,
            'gender' => $request->gender,
            'address' => $request->address,
            'photo' => $filename,
            'password' => $password,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return response()->json(['staus' => 'Success']);
    }

    public function destroy($id){
        $user = User::find($id);
        if ($user->photo != NULL) {
            # code...
            unlink(base_path('public/image/'. $user->photo));
        }
        $user->delete();

        return response()->json([
            'status' => 'Success'
        ]);
    }


    public function login(Request $request){

        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email' ,$request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            # code...
            $token = Str::random(40);
            $user->update(['api_token' => $token]);
            return response()->json([
                'status' => 'Success',
                'data' => $token
            ]);
        }
        return response()->json([
            'status' => 'Error',
        ]);
    }


    public function sendResetPassword(Request $request) {

        $this->validate($request, [
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email',$request->email)->first();
        $user->update(['reset_token' => Str::random(40)]);

        // reset password token
        Mail::to($user->email)->send(new ResetPasswordMail($user));

        return response()->json([
            'status' => 'Success',
            'data' => $user->reset_token,
        ]);

    }

    public function verifyResetPassword(Request $request,$token)
    {

        $this->validate($request, [
            'password' => 'required|string|min:6'
        ]);

        $user = User::where('reset_token',$token)->first();
            if($user) {
                $user->update([
                    'password' =>  app('hash')->make($request->password),
                ]);
                return response()->json([
                    'status' => 'Success',
                ]);
            }
            return response()->json([
                'status' => 'error',
            ]);

        // var_dump($request->password);
    }

    public function getUserLogin(Request $request){
        return response()->json([
            'status' => 'Success',
            'data' => $request->user()
        ]);
    }

    public function logout(Request $request){

        $user = $request->user();
        $user->update([
            'api_token' => null
        ]);
        return response()->json([
            'status' => 'Success',
        ]);

    }
}
