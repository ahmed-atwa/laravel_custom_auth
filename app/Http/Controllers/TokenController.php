<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TokenController extends Controller
{
    /**
     * Display a listing of the tokens for currently logged in user.
     * each token represent a login device.
     */
    public function index()
    {
        return [
            'tokensIndexSuccess' => \Auth::user()->tokens
        ];
    }

    /**
     * Create a token (user provide email and password for login)
     */
    public function store(Request $request)
    {
        $attempt = \Auth::attempt([
            'email'=> $request->email,
            'password'=> $request->password,
        ], ($request->remember == 'true'));

        if (!$attempt) {
            return [
                'tokensStoreFail' => '',
            ];
        }

        return [
            'tokensStoreSuccess' => [
                'user' => \Auth::user(),
                'token' => \Auth::getToken(),
            ]
        ];
    }

    /**
     * Display the specified resource.
     */
    /* public function show(string $id)
    {
        //
    } */

    /**
     * Update the specified resource in storage.
     */
    /* public function update(Request $request, string $id)
    {
        //
    } */

    /**
     * Delete current login token (the user is logging out)
     * id could be:
     *     null => to logout from current device
     *     string => numeric to logout from specific device by token id
     *     array => to logout from list of devices by tokens ids
     *
     * @param array|string|null $id
     */
    public function destroy(Request $request)
    {
        $id = $request->id;

        if(is_null($id)){
            $id = \Auth::getToken()->id;
        }

        $id = is_array($id) ? $id : [$id];

        \Auth::logoutByTokenId($id);

        return [
            'tokensDestroySuccess' => []
        ];
    }
}
