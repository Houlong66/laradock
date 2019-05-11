<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model; todo 两者的区别？
use App\Models\Common\Model;

class Ask extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::Class)
            ->withPivot([
                'item_type',
                'audit_text',
                'audit_time',
                'status',
            ])
            ->withTimestamps()
            ->using(AskUser::Class);
    }

    public function ask_items()
    {
        return $this->hasMany(AskUser::Class);
    }

    public function org(){
        return $this->belongsTo(Org::Class);
    }

    /**
     * 获得此请示的所有附件
     */
    public function attachments()
    {
        return $this->morphMany('App\Models\Attachment', 'works');
    }

    // 获取讨论板信息
    public function msg_boards(){
        return $this->hasMany('App\Models\MsgBoard', 'foreign_id', 'id');
    }

    // 获取对应类型
    public function asktype(){
        return $this->hasOne(AskType::class, 'id', 'ask_type');
    }
}
