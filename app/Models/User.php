<?php
//https://github.com/laravel/framework/blob/10.x/src/Illuminate/Foundation/Auth/User.php
//https://github.com/laravel/framework/blob/10.x/src/Illuminate/Auth/Authenticatable.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;


class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use  Authorizable, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /* protected $fillable = [
        'name',
        'email',
        'password',
    ]; */

    protected $guarded  = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        //'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        //'email_verified_at' => 'datetime',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }


    /**
     * a user has many tokens, each token represent login device
    */
    public function tokens(){
        return $this->hasMany(Token::class);
    }





    //==============================================================

    /**
     * Custom method to get token model of login
     *
     * @return \App\Models\Token|null
    */
    public function getRememberToken(){
        throw new \Exception('implement if needed! '.__FUNCTION__);
    }

    public function getAuthIdentifierName(){
        //return "username";
        throw new \Exception('implement if needed! '.__FUNCTION__);
    }
    public function getAuthIdentifier(){
        return $this->id;
        //throw new \Exception('implement if needed! '.__FUNCTION__);
    }
    public function setRememberToken($value){throw new \Exception('implement if needed! '.__FUNCTION__);}
    public function getRememberTokenName(){throw new \Exception('implement if needed! '.__FUNCTION__);}
}
