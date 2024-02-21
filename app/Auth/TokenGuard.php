<?php
namespace App\Auth;

use \Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\Authenticatable;
//use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use App\Models\Token;

class TokenGuard implements StatefulGuard//, SupportsBasicAuth
{
    //use GuardHelpers


    /**
     * The name of the guard.
     *
     * Corresponds to guard name in authentication configuration.
     *
     * @var string
     */
    public string $name;

    /**
     * user provider
     *
     * @var \App\Auth\TokenUserProvider
     */
    private $userProvider;


    /**
     * Http request
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    private string $inputKey;

    /**
     * The currently authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private $user;

    /**
     * The token model retreived from database.
     *
     * @var \App\Models\Token|null
     */
    private $token;


    /**
     * Create a new authentication guard.
     *
     * @param  string  $name
     * @param  \App\Auth\TokenUserProvider  $userProvider
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $config
     * @return void
     */
    public function __construct($name,
                                UserProvider $userProvider,
                                Request $request,
                                $config)
    {
        $this->name = $name;
        $this->userProvider = $userProvider;
        $this->request = $request;

        //$this->inputKey = isset($config['input_key']) ? $config['input_key'] : 'api_token';
        if(isset($config['input_key'])){
            $this->inputKey = $config['input_key'];
        }else{
            throw new \Exception('input_key required in auth guard configs');
        }
    }


    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = true)
    {
        $user = $this->userProvider->retrieveByCredentials($credentials);
        if(!$user) return false;

        $validated = $this->userProvider->validateCredentials($user, $credentials);
        if(!$validated) return false;


        $this->login($user, $remember);

        return true;
    }


    /**
     * Log a user into the application. called from $this->attempt()
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = true)
    {
        $token = Token::create([
            'user_id'    => $user->id,
            'token'     => hash('sha256', /* $user->email. */$user->password.strval(time())),
            'agent'    => $this->request->userAgent(),
            'ip'    => $this->request->ip(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);



        $lifeTime = $remember ? (time()+60*60*24*365*5) : 0;
        $secure = ($this->request->getScheme() === 'https') ? true : false;

        \Cookie::queue($this->inputKey, $token->token, $lifeTime, null, null, $secure, true);


        $this->user = $user;
        $this->token = $token;
        //$this->setUser($user);
    }


    /**
     * Log the user out of the application.
     * almost never used, i use logoutByTokenId()
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();
        $token = $this->token;

        if(!$user) return;


        $token->delete();

        \Cookie::queue(\Cookie::forget($this->inputKey));

        $this->user = null;
        $this->token = null;
    }


    /**
     * logout user from specific devices by deleting tokens
     *
     * @param array $id
     * @return void
    */
    public function logoutByTokenId($id){
        //convert $id into hash map for fast access.
        //$id = array_flip($id);
        $user = $this->user();
        $token = $this->token;

        if(!$user) return;

        //Token::where('user_id', $user->id)->whereIn('id', $id)->delete();
        $user->tokens()->whereIn('id', $id)->delete();

        //the rest of procedures are for current device only
        if(!in_array($token->id, $id)) return;


        //the user specified current device for logout too.
        \Cookie::queue(\Cookie::forget($this->inputKey));

        $this->user = null;
        $this->token = null;
    }


    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     * this method is called automatically at the beginning of the http
     * request by AuthServiceProvider, so always use Auth::check() and Auth::guest()
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately.
        if ($this->user) return $this->user;

        //resolve the user using provided token
        $tokenStr = $this->getTokenFromRequest();
        if(!$tokenStr) return null;

        $user = $this->userProvider->retrieveByToken(null, $tokenStr);
        if(!$user) return null;


        $this->user = $user;
        $this->token = $this->userProvider->token;

        return $user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    private function getTokenFromRequest()
    {
        $token = $this->request->query($this->inputKey);
        if($token) return $token;


        $token = $this->request->input($this->inputKey);
        if($token) return $token;


        $token = \Cookie::get($this->inputKey);
        if($token) return $token;


        $token = $this->request->header('Authorization');

        return $token;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }


    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return ! is_null($this->user);
    }


    /**
     * Custom method to get token model of login
     *
     * @return \App\Models\Token|null
     */
    public function getToken(){
        return $this->token;
    }




    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = []){
        throw new \Exception('implement if needed! '.__FUNCTION__);
	}

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed  $id
     * @param  bool  $remember
     * @return \Illuminate\Contracts\Auth\Authenticatable|bool
     */
    public function loginUsingId($id, $remember = false){
        throw new \Exception('implement if needed! '.__FUNCTION__);
	}

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return \Illuminate\Contracts\Auth\Authenticatable|bool
     */
    public function onceUsingId($id){
        throw new \Exception('implement if needed! '.__FUNCTION__);
	}

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember(){
        throw new \Exception('implement if needed! '.__FUNCTION__);
	}

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []){
        throw new \Exception('implement if needed! '.__FUNCTION__);
	}


    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function setUser(Authenticatable $user){
        throw new \Exception('implement if needed! '.__FUNCTION__);
	}


}
