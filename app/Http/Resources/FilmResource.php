<?php

namespace App\Http\Resources;

use App\Support\Iso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => (int) $this->duration,
            'year_of_issue' => (int) $this->year_of_issue,
            'age' => (int) $this->age,
            'link_img' => $this->link_img,
            'link_kinopoisk' => $this->link_kinopoisk,
            'link_video' => $this->link_video,
            'created_at' => Iso::format($this->created_at),
            'country' => [
                'id' => $this->country->id,
                'name' => $this->country->name,
            ],
            'categories' => $this->categories
                ->map(fn ($category) => ['id' => $category->id, 'name' => $category->name])
                ->values()
                ->all(),
            'ratingAvg' => round((float) ($this->rating_avg ?? 0), 2),
            'reviewCount' => (int) ($this->review_count ?? 0),
        ];
    }
}
