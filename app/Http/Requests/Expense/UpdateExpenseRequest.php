<?php

namespace App\Http\Requests\Expense;

use App\Models\Expense;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
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
        /** @var Expense $expense */
        $expense = $this->route('expense');

        return [
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')
                ->where('user_id', $expense->budget->owner_id)
                ->whereNull('deleted_at')],
            'amount' => ['sometimes', 'required', 'integer', 'min:1', 'max:'.config('budgets.max_amount')],
            'spent_on' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'note' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
