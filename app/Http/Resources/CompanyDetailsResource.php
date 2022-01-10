<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDetailsResource extends JsonResource
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
            'name'        => $this->name,
            'site'        => $this->site,
            'logo'        => $this->logo,
            'description' => $this->description,
            'size'        => $this->size,
            'job_numbers' => $this->job_numbers
        ];
    }
}
