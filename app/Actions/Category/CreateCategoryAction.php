<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCategoryAction
{
    /**
     * Create a category for the user, enforcing the per-user limit.
     *
     * The user row is locked so concurrent requests are serialized and can't
     * push the user past the limit.
     *
     * @param  array{name: string, icon: string, color: string}  $data
     *
     * @throws ValidationException
     */
    public function handle(User $user, array $data): Category
    {
        return DB::transaction(function () use ($user, $data): Category {
            User::whereKey($user->id)->lockForUpdate()->first();

            if ($user->categories()->count() >= Category::MAX_PER_USER) {
                throw ValidationException::withMessages([
                    'name' => __('categories.limit_reached', ['max' => Category::MAX_PER_USER]),
                ]);
            }

            try {
                return $user->categories()->create($data);
            } catch (UniqueConstraintViolationException) {
                throw ValidationException::withMessages([
                    'name' => __('categories.name_taken'),
                ]);
            }
        });
    }
}
