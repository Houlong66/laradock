<?php

namespace App\Http\Requests;

class ApplyRequest extends Request
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
                switch ($route_name) {
                    case 'my.apply.joinOrgWithLogin':
                        $rules = [
                            'dept_id' => 'bail|integer|exists:depts,id',
                            'tel' => [
                                'numeric',
                                'regex:/1[0-9]{10}/',
                                ]
                        ];
                        break;

                    case 'my.apply.signOrgWithLogin':
                        $rules = [
                            'name' => 'bail|required|max:40',
                            'region' => 'bail|required|max:40'
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