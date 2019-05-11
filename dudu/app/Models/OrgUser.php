<?php

namespace App\Models;

use App\Models\Common\Pivot;

class OrgUser extends Pivot
{
    public function org()
    {
        return $this->belongsTo(Org::class);
    }

    public function agreed_user(){
        return $this->belongsTo(User::Class,'agreed_user','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
