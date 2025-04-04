<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstituteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'institute_id'                    =>  $this->inst_sl_pk,
            'institute_code'                  =>  $this->institute_code,
            'institute_name'                  =>  $this->institute_name,
            'institute_address'    =>  $this->institute_address,
            'institute_active'                =>  $this->is_active
        ];
    }
}
