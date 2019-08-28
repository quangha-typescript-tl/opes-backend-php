<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HashTagContent extends Model
{
    //    use Notifiable;
    protected $table = 'hash_tag_content';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'content_id', 'hash_tag'
    ];
}
