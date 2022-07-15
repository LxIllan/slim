<?php

declare(strict_types=1);

namespace App\Application\Model;

use Illuminate\Database\Eloquent\Model;
 
class User extends Model 
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'email' ,'pass'];
}