<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\Resource;

class User extends Resource
{

    /**
     * @inheritDoc
     */
    public function toArray($request)
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'display_name' => $this->display_name,
            'status' => $this->status,
            'description' => $this->description,
            'profile_image' => Image::make($this->profile_image)
        ];
    }
}