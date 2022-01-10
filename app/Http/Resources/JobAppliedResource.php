<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobAppliedResource extends JsonResource
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
            'id'                  => $this->id,
            'title'               => $this->title,
            'salary_range'        => $this->salary_range,
            'requirements'        => $this->requirements,
            'description'         => $this->description,
            'years_of_experience' => $this->years_of_experience,
            'company_status'      => $this->pivot->status,
            'resume'              => $this->pivot->resume,
            'company'             => new CompanyResource($this->company),
            'type'                => new JobTypeResource($this->type),
            'category'            => new CategoryResource($this->category),
            'location'            => new LocationResource($this->location)
        ];
    }
}
