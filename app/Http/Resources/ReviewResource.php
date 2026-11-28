<?php

namespace App\Http\Resources;

use App\Support\Iso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'film' => [
                'id' => $this->film->id,
                'name' => $this->film->name,
            ],
            'message' => $this->message,
            'is_approved' => (int) $this->is_approved,
            'created_at' => Iso::format($this->created_at),
        ];
    }
}
