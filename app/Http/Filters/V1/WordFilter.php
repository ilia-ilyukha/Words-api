<?php

namespace App\Http\Filters\V1;

class WordFilter extends QueryFilter {

    public function capital($value){
        return $this->builder->whereIn('words_capital_id', explode(',', $value));
    }

    public function rand($value){
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? $this->builder->inRandomOrder() : '';
    }

    public function limit($value){
        return $this->builder->limit($value);
    }

}