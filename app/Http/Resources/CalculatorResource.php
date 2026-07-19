<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Calculator
 */
class CalculatorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'icon' => $this->icon,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'input_schema' => $this->input_schema,
            'result_schema' => $this->result_schema,
            'meta' => [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
                'keywords' => $this->meta_keywords,
                'og_image' => $this->og_image,
                'canonical_url' => $this->canonical_url,
            ],
            'is_premium' => (bool) $this->is_premium,
            'is_featured' => (bool) $this->is_featured,
            'views_count' => $this->views_count,
            'usage_count' => $this->usage_count,
            'faqs' => $this->whenLoaded('faqs', fn () => $this->faqs->map(fn ($faq) => [
                'question' => $faq->question,
                'answer' => $faq->answer,
            ])),
            'examples' => $this->whenLoaded('examples', fn () => $this->examples->map(fn ($example) => [
                'title' => $example->title,
                'inputs' => $example->inputs,
                'outputs' => $example->outputs,
                'explanation' => $example->explanation,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
