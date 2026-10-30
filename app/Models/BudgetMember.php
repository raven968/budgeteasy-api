<?php

namespace App\Models;

use App\Enums\BudgetRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BudgetMember extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => BudgetRole::class,
        ];
    }
}
