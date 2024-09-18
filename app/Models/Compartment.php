<?php

namespace App\Models;

use App\Traits\Rentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compartment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Rentable;

    protected $guarded = ['id'];

}
