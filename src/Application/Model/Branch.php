<?php

declare(strict_types=1);

namespace App\Application\Model;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branch';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'location', 'phone_number'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'logo_path' => '',
        'ticket_number' => 1,
        'note' => 'Write notes here!',
        'admin_id' => 0
    ];
}