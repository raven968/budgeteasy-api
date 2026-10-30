<?php

namespace App\Http\Requests\Budget;

use App\Http\Requests\Concerns\ResolvesMonth;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowBudgetSummaryRequest extends FormRequest
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
        ];
    }
}
