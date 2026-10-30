<?php

namespace App\Http\Requests\Expense;

use App\Models\Budget;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
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
        /** @var Budget $budget */
        $budget = $this->route('budget');

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')
                ->where('user_id', $budget->owner_id)
                ->whereNull('deleted_at')],
            'amount' => ['required', 'integer', 'min:1', 'max:'.config('budgets.max_amount')],
            'spent_on' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
