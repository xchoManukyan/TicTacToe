<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = ['user_id', 'wined', 'ended', 'draw', 'boxes_count'];

    /**
     * @return HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany(Step::class, 'game_id', 'id');
    }
}
