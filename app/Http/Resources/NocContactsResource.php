<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NocContactsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"                    => $this->id,
            "type"                  => $this->type,
            "first_name"            => $this->first_name,
            "last_name"             => $this->last_name,
            "email"                 => $this->email,
            "mobile"                => $this->mobile,
            "emirates_id"           => $this->emirates_id,
            "passport_number"       => $this->passport_number,
            "visa_number"           => $this->visa_number,
            "emirates_document_url" => $this->emirates_document_url ? env('AWS_URL') . '/' . $this->emirates_document_url : null,
            "visa_document_url"     => $this->visa_document_url ? env('AWS_URL') . '/' . $this->visa_document_url : null,
            "passport_document_url" => $this->passport_document_url ? env('AWS_URL') . '/' . $this->passport_document_url : null,
            "agent_email"           => $this->agent_email,
            "agent_phone"           => $this->agent_phone,
            "title_deed"            => $this->title_deed ? env('AWS_URL') . '/' . $this->title_deed : null,
            "poa_document"          => $this->poa_document ? env('AWS_URL') . '/' . $this->poa_document : null,
        ];
    }
}
