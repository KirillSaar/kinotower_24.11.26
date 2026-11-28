<?php

namespace App\Http\Resources;

use App\Support\Iso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'film' => [
                'id' => $this->film->id,
                'name' => $this->film->name,
            ],
            'score' => (int) $this->ball,
            'created_at' => Iso::format($this->created_at),
        ];
    }
}
