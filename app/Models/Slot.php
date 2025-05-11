<?php

namespace App\Models;

use Carbon\Carbon;
//use Illuminate\Database\Eloquent\Model;

class Slot /** extends Model **/
{
    public int $duration = 30; //min

    public ?int $userId = null;

    public ?Carbon $datetime = null;

    public ?int $appointmentId = null;


}
