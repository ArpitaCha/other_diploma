<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarksScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            "id" => $this->id,
            "inst_id" => $this->config_for_inst_id,
            "semester" => $this->semester,
            "start_date" => $this->start_at,
            "end_date" => $this->end_at,
            "type" => $this->mark_type,
            "schedule_desc" => $this->config_for,
            
        ];
        
    }
}
