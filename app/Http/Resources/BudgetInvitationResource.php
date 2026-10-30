<?php

namespace App\Http\Resources;

use App\Models\BudgetInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BudgetInvitation
 */
class BudgetInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'budget_id' => $this->budget_id,
            'code' => $this->code,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
