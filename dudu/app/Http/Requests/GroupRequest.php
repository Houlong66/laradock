<?php

namespace App\Http\Requests;

use App\Models\Group;

class GroupRequest extends Request
{
    public function rules()
    {
        $rules = [];
        $route_name = $this->route()->getName();

        switch ($route_name) {
            case 'my.group.create':
                $rules = [
                    'name' => 'bail|required',
                    'type' => 'bail|required|integer'
                ];
                break;
            default:
                break;
        }
        return $rules;
    }
}
