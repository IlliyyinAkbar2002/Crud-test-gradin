<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Courier extends Model
{
    use HasFactory;
    //

    public const VEHICLE_TYPES = ['Motorcycle', 'Car', 'Truck'];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'vehicle_type',
        'license_plate',
        'level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        foreach (preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY) as $term) {
            // ILIKE = case-insensitive di PostgreSQL; addcslashes menetralkan % dan _ dari input
            $query->where('name', 'ILIKE', '%'.addcslashes($term, '%_\\').'%');
        }

        return $query;
    }

    public function scopeOfLevels(Builder $query, array $levels): Builder
    {
        return $query->whereIn('level', $levels);
    }
}
