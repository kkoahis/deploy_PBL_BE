<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // pagination

        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'name' => $this->name,
            'email' => $this->email,
            'description' => $this->description,
            'address' => $this->address,
            'hotline' => $this->hotline,
            'room_total' => $this->room_total,
            'parking_slot' => $this->parking_slot,
            'bathrooms' => $this->bathrooms,
            'rating' => $this->rating,
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),

            'images' => $this->hotelImage,
            'categories' => $this->category,
            // get all rooms of categories
            'rooms' => $this->category->map(function ($category) {
                return $category->room;
            })
        ];
    }
}
