<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthorityUser extends Model
{
    protected $table   = 'authority_user';
    public $timestamps = false;

    protected $fillable = array(
        'id', 'authority_code', 'user_id',
    );

    public static function getAuthority($id) {
        $result = AuthorityUser::join('users', 'users.id', '=', 'authority_user.user_id')
            ->join('authority', 'authority.code', '=', 'authority_user.authority_code')
            ->where('authority_user.user_id', '=', $id)
            ->pluck('authority.code');

        return $result ? $result : [];
    }

    public static function getAuthorityUser($id, $authority) {
        $result = AuthorityUser::join('authority', 'authority.code', '=', 'authority_user.authority_code')
            ->where('authority_user.user_id', '=', $id)
            ->where('authority_user.authority_code', '=', $authority)
            ->first();

        return $result ? true : false;
    }
}
