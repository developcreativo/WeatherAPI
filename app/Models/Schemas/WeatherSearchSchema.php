<?php

namespace App\Models\Schemas;

/**
 * @OA\Schema(
 *     schema="WeatherSearch",
 *     required={"id", "user_id", "city", "country", "temperature", "condition", "wind_speed", "humidity", "local_time"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="city", type="string", example="London"),
 *     @OA\Property(property="country", type="string", example="United Kingdom"),
 *     @OA\Property(property="temperature", type="number", format="float", example=15.5),
 *     @OA\Property(property="condition", type="string", example="Partly cloudy"),
 *     @OA\Property(property="wind_speed", type="number", format="float", example=10.2),
 *     @OA\Property(property="humidity", type="integer", example=70),
 *     @OA\Property(property="local_time", type="string", format="date-time"),
 *     @OA\Property(property="raw_data", type="object"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class WeatherSearchSchema
{
    // This class is only used for Swagger documentation, not for actual data storage
}
