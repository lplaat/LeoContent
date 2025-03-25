<?php

namespace Src\App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'job';

    protected $fillable = [
        'status',
        'type',
        'parent_id'
    ];

    function getTextStatusAttribute()
    {
        if($this->status == 0) {
            return 'Waiting in queue';
        } else if($this->status == 1) {
            return 'is in progress';
        } else if($this->status == 2) {
            return 'Done';
        }
    }

    function getTypeNameAttribute()
    {
        if($this->type == 1) {
            return 'Directory Scan';
        }
    }

    function getColorStatusAttribute()
    {
        if($this->status == 0) {
            return 'secondary';
        } else if($this->status == 1) {
            return 'warning';
        } else if($this->status == 2) {
            return 'success';
        }
    }
}