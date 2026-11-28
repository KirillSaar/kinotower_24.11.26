<?php

namespace App\Http\Resources;

use App\Support\Iso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'fio' => $this->user->fio,
            ],
            'message' => $this->message,
            'created_at' => Iso::format($this->created_at),
        ];
    }
}
