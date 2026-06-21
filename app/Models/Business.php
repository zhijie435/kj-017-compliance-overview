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

    public function setEinAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['ein'] = $value;
            return;
        }
        $digits = preg_replace('/\D/', '', $value);
        if (strlen($digits) >= 2) {
            $this->attributes['ein'] = substr($digits, 0, 2) . '-' . substr($digits, 2, 7);
        } else {
            $this->attributes['ein'] = $value;
        }
    }

    public function cases()
    {
        return $this->hasMany(BusinessCase::class);
    }
}
