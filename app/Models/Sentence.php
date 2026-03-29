<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sentence extends Model
{
    use HasFactory;

    protected $fillable = ['word_id', 'description_DE', 'description_RU'];

    public $timestamps = false;

    public function word()
    {
        return $this->belongsTo(Word::class);
    }
}
