<?php

namespace App\Http\Resources;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($item) {
            if (isset($item['status'])) {
                $item['status'] = ['id' => $item['status'], 'label' => User::getStatusName($item['status'])];
            }
            if (isset($item['game_position'])) {
                $item['game_position'] = ['label' => $item['game_position'], 'description' => User::getPositionName($item['game_position'])];
            }
            return $item;
        })->toArray();
    }

    public function with($request)
    {
        return [
            'status' => true,
            'message' => 'Liste des utilisateurs',
        ];
    }
}
