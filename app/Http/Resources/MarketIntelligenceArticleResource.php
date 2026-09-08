<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketIntelligenceArticleResource extends JsonResource
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
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'category' => $this->category,
            'sentiment' => $this->sentiment,
            'source' => $this->source,
            'cover_image' => $this->cover_image,
            'published_at' => optional($this->published_at)?->toDateTimeString(),
            'author' => $this->whenLoaded('author', fn (): ?array => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ] : null),
        ];
    }
}
