<?php

declare(strict_types=1);

namespace App\Application\Model;

use Illuminate\Database\Eloquent\Model;

class Food extends Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'food';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'quantity', 'quantity_notif', 'cost', 'category_id', 'branch_id'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_notif_sent' => 0,
        'is_showed_in_index' => 1
    ];
}