<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
//    use Notifiable;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'departmentName', 'description'
    ];
}
