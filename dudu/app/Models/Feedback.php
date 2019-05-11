<?php

namespace App\Models;

use App\Models\Common\Model;

class Feedback extends Model
{
    public function attachments()
    {
        return $this->morphMany('App\Models\Attachment', 'works');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
