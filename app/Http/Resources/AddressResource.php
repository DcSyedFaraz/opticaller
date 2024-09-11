<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'company_name' => $this->company_name,
            'salutation' => $this->salutation,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'street_address' => $this->street_address,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'country' => $this->country,
            'website' => $this->website,
            'phone_number' => $this->phone_number,
            'email_address_system' => $this->email_address_system,
            'email_address_new' => $this->email_address_new,
            'feedback' => $this->feedback,
            'follow_up_date' => $this->follow_up_date,
            'contact_id' => $this->contact_id,
            'sub_project_id' => $this->sub_project_id,
            'sub_project_title' => optional($this->subproject)->title,
            'project_id' => optional($this->project)->id,
            'project_title' => optional($this->project)->title,

        ];
    }
}
