<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'avatar' => $this->avatar === 'FACEBOOK' ? ('http://localhost/storage/profilepic/'. $this->id . '.jpg') : $this->avatar,
            'created_at' => $this->created_at,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'id' => $this->id,
            'name' => $this->name,
            'updated_at' => $this->updated_at,
            'lol' => 'lol'
        ];
    }
}
