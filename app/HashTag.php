<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HashTag extends Model
{
    //    use Notifiable;
    protected $table = 'hash_tag';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'hash_tag', 'created_at', 'updated_at'
    ];

    public static function getListHashTag($hashTag)
    {
        $tag = HashTag::select('hash_tag')
            ->where('hash_tag',  $hashTag)
            ->first();

        $result = HashTag::select('hash_tag')
            ->where('hash_tag', 'like', '%' . $hashTag . '%')
            ->get();

        return [
            'exit' => ($tag) ? true: false,
            'hashTag' => $result
        ];
    }
}
