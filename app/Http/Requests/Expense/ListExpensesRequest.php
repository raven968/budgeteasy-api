<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\Concerns\ResolvesMonth;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListExpensesRequest extends FormRequest
{
    use ResolvesMonth;

    /**
     * Authorization is handled by the route's `can` middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
            'category_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
        ];
    }
}
