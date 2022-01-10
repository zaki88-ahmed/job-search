<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'gender'            => $this->gender,
            'marital_status'    => $this->marital_status,
            'military_status'   => $this->military_status,
            'nationality'       => $this->nationality,
            'resume'            => $this->resume,
        ];
    }
}
