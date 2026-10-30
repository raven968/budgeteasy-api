<?php

namespace App\Rules;

use App\Models\Category;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * The name must not match, ignoring case, another active category of the user.
 */
class UniqueCategoryName implements ValidationRule
{
    public function __construct(
        private User $user,
        private ?Category $ignore = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = $this->user->categories()
            ->whereRaw('lower(name) = ?', [mb_strtolower((string) $value)])
            ->when($this->ignore, fn ($query) => $query->whereKeyNot($this->ignore->getKey()))
            ->exists();

        if ($exists) {
            $fail('categories.name_taken')->translate();
        }
    }
}
