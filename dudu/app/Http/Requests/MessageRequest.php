<?php

namespace App\Http\Requests;

class MessageRequest extends Request
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