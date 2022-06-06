<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => [
                Rule::exists('blogs')->where(function ($query) {
                    return $query->where('id', $blog->id);
                }),
            ],
            'name' => 'min:2',
            'domain' => 'url',
            'owner_id' => [
                Rule::exists('users')->where(function ($query) {
                    return $query->where('owner_id', auth()->id());
                }),
            ],
        ];
    }
}
