<?php

namespace App\Http\Requests;

use App\Models\User;

class UserRequest extends Request
{
    public function rules()
    {
        $rules = [];
        $route_name = $this->route()->getName();

        switch ($this->method()) {
            // INDEX
            case 'GET':
                switch ($route_name) {
                    case 'my.user.getTelCodeWithLogin':
                        $rules = [
                            'tel' => [
                                'required',
                                'numeric',
                                'regex:/1[0-9]{10}/'
                            ],
                        ];
                        break;
                    default:
                        $rules = [
                        ];
                        break;
                }
                break;
            // CREATE
            case 'POST':
                switch ($route_name) {
                    case 'my.user.joinOrgWithLogin':
                        $rules = [
                            'org_id' => 'bail|required|integer|exists:orgs,id',
                            'role_id'  => 'bail|required|integer|exists:roles,id',
                            'dept_id' => 'bail|integer|exists:depts,id',
                        ];
                        break;
                    default:
                        $rules = [
                        ];
                        break;
                }
                break;
            // UPDATE
            case 'PUT':
            case 'PATCH':
                switch ($route_name) {
                    default:
                        $rules = [
                            'avatar'    => 'url',
                            'sex'       => 'integer|max:2|min:1',
                            'tel'       => [
                                'numeric',
                                'regex:/1[0-9]{10}/'
                            ],
                            'email'     => 'email',
                            'qq'        => 'numeric',
                        ];
                }
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