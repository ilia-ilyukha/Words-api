<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWordRequest extends FormRequest
{
    // TODO: add authorization logic here
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'DE' => ['required', 'string'],
            'RU' => ['required', 'string'],
            'words_capital' => ['sometimes', 'integer'],
        ];
    }

    public function mappedAttributes(array $otherAttributes = [])
    {
        $attributeMap = array_merge([
            'DE' => 'DE',
            'RU' => 'RU',
            'words_capital' => 'words_capital_id',
        ], $otherAttributes);

        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }
        return $attributesToUpdate;
    }
}
