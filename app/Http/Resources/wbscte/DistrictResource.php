<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'district_id'           =>  $this->district_id_pk,
            'district_name'         =>  $this->district_name,
            'state_name'            =>  $this->state->state_name,
            'state_id'              =>  $this->state_id_fk,
            'schcd'                 =>  $this->schcd,
            'google_district_name'  =>  $this->google_district_name,
            'lgd_code'              =>  $this->lgd_code,

            //'block_municipality'  =>  BlockMunicipalityResource::collection($this->whenLoaded('district')),
        ];
    }
}
