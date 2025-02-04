<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class adminController extends Controller
{
    public function adminPage(){
        if(Auth::user()->role == 'admin'){
            return view('adminPage');
        }
        else{
            return redirect()->route('loginForm')->with('status','You are not authorized to access this page');
        }
    }
    public function createUserForm(){
        return view('createUserForm');
    }
    public function createUser(Request $request){
        DB::beginTransaction();
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'role' => 'required|string',
                'password' => 'required|string'
            ]);
            $user = User::create([
                'name' => $validated['name'],
                'password' => bcrypt($validated['password']),
                'role' => $validated['role']
            ]);
            DB::commit();
            return redirect()->route("adminPage")->with('status', 'New user created!');
        }
        catch(\Exception $e){
            DB::rollBack();
            \Log::error('Fail to create user with the following message'.$e->getMessage());
            return redirect()->route("createUserForm")->with('status', 'Failed to create user. Maybe the name already existed?');
        }
    }
    public function updateUserForm($id){
        $user = User::find($id);
        return view('updateUserForm', ['user' => $user]);
    }
    public function updateUser(Request $request, $id){
        DB::beginTransaction();
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'password' => 'required|string',
                'role' => 'required|string',
            ]);
            $user = User::find($id);
            $user->name = $validated['name'];
            $user->password = bcrypt($validated['password']);
            $user->role = $validated['role'];
            $user->save();
            DB::commit();
            return redirect()->route("viewUser")->with('status', 'User has been updated');
        }
        catch(\Exception $e){
            DB::rollback();
            \Log::error('Fail to update user with the following message'.$e->getMessage());
            return redirect()->route("updateUserForm",$id)->with('status','An error occured while updating user');
        }
    }
    public function viewUser(){
        $users = User::all();
        return view('viewUser', ['users' => $users]);
    }

    public function loginForm(){
        return view('loginForm');
    }
    public function login(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'password' => 'required|string'
            ]);
            if(Auth::attempt($validated)){
                return redirect()->route('main');
            }
            else{
                return redirect()->route('loginForm')->with('status','Login failed');
            }
        }
        catch(\Exception $e){
            \Log::error('Fail to login with the following message'.$e->getMessage());
            return redirect()->route('loginForm')->with('status','Login failed, what went wrong?');
        }
    }
    public function logout(){
        Auth::logout();
        return redirect()->route('loginForm');
    }

    public function main(){
        return redirect()->route('viewSparepart');
    }
}
