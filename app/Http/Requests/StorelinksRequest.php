<?php

namespace App\Http\Requests;

use App\Models\Links;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class StoreLinksRequest extends FormRequest
{
    private int $maxLength = 0;
    public function __construct()
    {
        parent::__construct();

        // Cache key length limit: 255 characters (Laravel default)
        $this->maxLength = 254 - (int) Str::length(config('cache.prefix') . '-:link-');
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // If slug is empty, generate one from title
        if (empty($this->slug)) {
            $this->merge([
                'slug' => $this->generateUniqueSlug()
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'target_url' => "required|url|max:255",
            'slug' => [
                "required",
                "unique:links,slug",
                "max:{$this->maxLength}",
                "regex:/^[A-Za-z0-9-]+$/",
            ],
            'is_active' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'target_url.required' => 'The target url field is required.',
            'target_url.url' => 'The target url must must be a valid URL.',
            'target_url.max' => 'The target url must not exceed 255 characters.',
            'slug.unique' => 'This slug is already registered.',
            'slug.max' => "The slug must not exceed {$this->maxLength} characters.",
            'is_active.required' => 'The is active field is required.',
            'is_active.boolean' => 'The Is active field must be either 0 or 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    private function generateUniqueSlug(): string
    {
        do {
            $slug = Str::random($this->maxLength);
            $exists = Links::where('slug', $slug)->exists();
        } while ($exists);

        return $slug;
    }
}
