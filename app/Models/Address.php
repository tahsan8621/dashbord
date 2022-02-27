<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'first_name',
        'last_name',
        'company_name',
        'street_name',
        'email',
        'address',
        'city',
        'postal_code',
        'country',
        'cell_number',
        'status',
        'fax'
    ];
}
