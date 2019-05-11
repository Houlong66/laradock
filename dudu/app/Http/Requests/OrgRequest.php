<?php

namespace App\Http\Requests;

use App\Models\Org;

class OrgRequest extends Request
{
    public function rules()
    {
        $rules = [];
        $route_name = $this->route()->getName();

        switch ($this->method()) {
            // INDEX
            case 'GET':
                break;
            // CREATE
            case 'POST':
                break;
            // UPDATE
            case 'PUT':
            case 'PATCH':
                break;
            case 'DELETE':
            default:
        }
        return $rules;
    }

    public function messages()
    {
        return [
            
        ];
    }
}