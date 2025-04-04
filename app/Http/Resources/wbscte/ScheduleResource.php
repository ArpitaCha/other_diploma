<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
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
            'schedule_type'=>$this->config_for,
            'inst_name'=>optional($this->institute)->institute_name
            
        ];
    }
}
