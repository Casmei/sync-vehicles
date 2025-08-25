<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'external_updated_at' => $this->external_updated_at,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'version' => $this->version,
            'year' => [
                'model' => data_get($this, 'year.model'),
                'build' => data_get($this, 'year.build'),
            ],
            'optionals_json' => $this->optionals_json ?? [],
            'fotos_json' => $this->fotos_json ?? [],
            'doors' => $this->doors,
            'board' => $this->board,
            'chassi' => $this->chassi,
            'transmission' => $this->transmission,
            'km' => $this->km,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'color' => $this->color,
            'fuel' => $this->fuel,
            'sold' => (bool) $this->sold,
            'category' => $this->category,
            'url_car' => $this->url_car,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
