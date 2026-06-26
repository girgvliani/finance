<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the transaction into its JSON representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'type'        => $this->type,
            'amount'      => $this->amount,
            'status'      => $this->status,
            'event_date'  => $this->event_date?->toDateString(),
            'deadline'    => $this->deadline?->toDateString(),
            'categories'  => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'color' => $c->color,
            ])),
        ];
    }
}
