<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchComplianceResource extends JsonResource
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
            'batch_id' => $this->batch_id,
            'user_id' => $this->user_id,
            'compliance_type' => $this->compliance_type,
            'status' => $this->status,
            'certificate_number' => $this->certificate_number,
            'issued_by' => $this->issued_by,
            'issued_at' => optional($this->issued_at)?->toDateString(),
            'expires_at' => optional($this->expires_at)?->toDateString(),
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}
