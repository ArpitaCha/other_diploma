<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       
        $start_date = date('jS-M-Y', strtotime($this->noti_start_date));
        $end_date = date('jS-M-Y', strtotime($this->noti_end_date));

        return [
            'id' => $this->noti_id,
            'msg' => $this->noti_message,
            'start_date' => date('jS-M-Y', strtotime($this->noti_start_date)),
            'end_date' => date('jS-M-Y', strtotime($this->noti_end_date)),
            'message' => "{$this->noti_message} starting from {$start_date} to {$end_date}",
            'is_active' => (bool)$this->noti_active,
        ];
    }
}
