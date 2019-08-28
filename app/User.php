<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userName', 'email', 'password', 'temporaryPassword ', 'avatar', 'department', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
    * Get the identifier that will be stored in the subject claim of the JWT.
    *
    * @return mixed
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this->id,
                'userName' => $this->userName,
                'email' => $this->email,
                'department' => $this->department,
            ]
        ];
    }

    public static function getUserSession($userId)
    {
        $result = User::select('users.id', 'users.userName', 'users.email', 'users.department', 'departments.departmentName', 'users.avatar', 'users.status')
            ->leftJoin('departments', 'departments.id', '=', 'users.department')
            ->where('users.id', $userId)
            ->first();
        return $result;
    }

    public static function getUsers($name, $department, $status)
    {
        $result = User::where(function ($q) use ($name, $department, $status) {
                $q->where(function ($query) use ($department) {
                    if ($department) {
                        $query->whereIn('department', $department);
                    }
                })->where(function ($query) use ($status) {
                    if ($status) {
                        $query->whereIn('status', $status);
                    }
                })->where(function ($query) use ($name) {
                    if ($name) {
                        $query->where('userName', 'like', '%' . $name . '%')
                            ->orWhere('email', 'like', '%' . $name . '%');
                    }
                });
            })
            ->get();
        return $result;
    }

    public static function getAuthority($id) {
        $result = AuthorityUser::getAuthority($id);
        return $result;
    }
}
