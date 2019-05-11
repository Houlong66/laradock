<?php

namespace App\Models;

use App\Models\Common\Model;

class Article extends Model
{
    public function author(){
        return $this->hasMany(AdminUser::Class,'id','author');
    }
}
