<?php

namespace App\Http\Requests\Concerns;

use Carbon\CarbonImmutable;

/**
 * For requests with an optional `month` query parameter in `Y-m` format.
 */
trait ResolvesMonth
{
    /**
     * The requested month, defaulting to the current one, as its first day.
     */
    public function month(): CarbonImmutable
    {
        $month = $this->validated('month');

        return $month
            ? CarbonImmutable::createFromFormat('!Y-m', $month)
            : CarbonImmutable::now()->startOfMonth();
    }
}
