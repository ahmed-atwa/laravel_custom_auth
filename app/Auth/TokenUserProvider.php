<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use \Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;


class TokenUserProvider implements UserProvider{
    /**
     * @var \Illuminate\Http\Request
    */
    private $request;


    /**
     * The name of the login "column" in persistent storage.
     * email,username,phone..etc
     * it will try matching with each column so user can enter
     * email or username in the field along with password and it will work.
     *
     * @var array
     */
    private $storageKeys;

    /**
     * The token model retreived from database.
     *
     * @var \App\Models\Token|null
     */
    public $token;

    /**
     * instantiated when defining user provider at App\Providers\AuthServiceProvider
    */
    public function __construct(Request $request, $config) {
        $this->request = $request;


        if(isset($config['storage_keys'])){
            $this->storageKeys = is_array($config['storage_keys'])
                                    ? $config['storage_keys'] : [$config['storage_keys']];
        }else{
            throw new \Exception('storage_keys required in auth guard configs');
            //$this->storageKeys = ['email'/* , 'username', 'phone' */];
        }
    }


    /**
     * Retrieve a user by their unique identifier.
     * The retrieveById function typically receives a key representing
     * the user, such as an auto-incrementing ID from a MySQL database.
     * The Authenticatable implementation matching the ID should be retrieved
     * and returned by the method.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier){
        return User::where('id',$identifier)->first();
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $tokenStr){
        //first call from TokenGuard::user()
        $token = Token::where('token', $tokenStr)->first();
        if(!$token) return null;

        $this->token = $token;

        //so far i don't know why $identifier parameter exist so i will ignore it
        if($identifier) throw new \Exception('identifier used at retrieveByToken');
        //if((!$token) || ($token->user_id !== $identifier)) return;

        return $this->retrieveById($token->user_id);


        //$token = $this->token->with('user')->where($identifier, $token)->first();
		//return $token && $token->user ? $token->user : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken (Authenticatable $user, $token) {
		// update via remember token not necessary
        throw new \Exception('implement if needed! '.__FUNCTION__);
	}

    /**
     * Retrieve a user by the given credentials.
     * The retrieveByCredentials method receives the array of credentials passed
     * to the Auth::attempt method when attempting to authenticate with an application.
     * The method should then "query" the underlying persistent storage for
     * the user matching those credentials. Typically, this method will run
     * a query with a "where" condition that searches for a user record
     * with a "username" matching the value of $credentials['username'].
     * The method should return an implementation of Authenticatable.
     * !!This method should not attempt to do any password validation or authentication.!!
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials){
        $storageKeys = $this->storageKeys;

        //first call to this method is from TokenGuard::attempt()
        $user = User::where(array_shift($storageKeys), $credentials['email']);//->first();

        foreach($storageKeys as $key){
            $user->orWhere($key, $credentials['email']);
        }

        return $user->first();
    }

    /**
     * Validate a user against the given credentials.
     * The validateCredentials method should compare the given $user
     * with the $credentials to authenticate the user.
     * For example, this method will typically use the Hash::check method
     * to compare the value of $user->getAuthPassword() to the value of $credentials['password'].
     * This method should return true or false indicating whether the password is valid.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials){
        return \Hash::check($credentials['password'] , $user->getAuthPassword());
    }

}
