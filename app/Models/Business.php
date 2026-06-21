<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name', 'uscc', 'ein', 'legal_rep', 'registered_capital', 'establish_date',
        'address', 'scope', 'industry', 'region', 'country',
    ];

    protected function casts(): array
    {
        return [
            'establish_date' => 'date',
        ];
    }

    public function cases()
    {
        return $this->hasMany(BusinessCase::class);
    }
}
