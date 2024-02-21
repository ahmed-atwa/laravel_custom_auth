<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /* public function index()
    {
        //
    } */

    /**
     * Store a newly created resource in storage.
     * register new user
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|bail',
            'email'=> 'required|email|unique:users,email|bail',
            'password'=> 'required',
        ]);

        if($validator->fails()){
            return [
                'usersStoreFail' => $validator->errors()
            ];
        }


        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> \Hash::make($request->password),
        ]);

        \Auth::login($user, true);


        return [
            'usersStoreSuccess' => [
                'user' => $user,
                'token' => $user->getRememberToken(),
            ]
        ];
    }

    /**
     * get current logged in user along with login token
     */
    public function show()
    {
        return [
            'usersShowSuccess' => [
                'user' => \Auth::user(),
                'token' => \Auth::getToken(),
            ]
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    /* public function update(Request $request, string $id)
    {
        //
    } */

    /**
     * Remove the specified resource from storage.
     */
    /* public function destroy(string $id)
    {
        //
    } */
}
