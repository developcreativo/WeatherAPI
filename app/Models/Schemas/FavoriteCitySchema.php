<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *     schema="FavoriteCity",
 *     required={"id", "user_id", "city", "country"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="city", type="string", example="London"),
 *     @OA\Property(property="country", type="string", example="United Kingdom"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class FavoriteCitySchema
{
    // This class is only used for Swagger documentation, not for actual data storage
}
