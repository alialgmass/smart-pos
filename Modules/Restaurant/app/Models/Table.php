<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Restaurant\Database\Factories\TableFactory;
use Modules\Restaurant\Enums\TableStatus;
use Modules\Shared\Concerns\BelongsToTenant;

class Table extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'restaurant_tables';

    protected $fillable = [
        'tenant_id',
        'name',
        'capacity',
        'status',
        'position_x',
        'position_y',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
            'capacity' => 'integer',
            'position_x' => 'integer',
            'position_y' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    protected static function newFactory(): TableFactory
    {
        return TableFactory::new();
    }
}
