<?php

namespace App\Models;

use App\SourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'external_id',
        'external_updated_at',
        'type',
        'brand',
        'model',
        'version',
        'year',
        'optionals_json',
        'fotos_json',
        'doors',
        'board',
        'chassi',
        'transmission',
        'km',
        'price',
        'old_price',
        'color',
        'fuel',
        'sold',
        'category',
        'url_car',
        'source',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'year' => 'array',
        'optionals_json' => 'array',
        'fotos_json' => 'array',
        'km' => 'integer',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'sold' => 'boolean',
        'external_updated_at' => 'datetime',
        'source' => SourceType::class,
    ];

    protected static function booted()
    {
        static::creating(function ($vehicle) {
            if (!$vehicle->id) {
                $vehicle->id = (string) Str::uuid();
            }
        });
    }
}
