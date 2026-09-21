<?php

use App\Enums\GeneroEnum;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

$validator = Validator::make(
    ['genero' => ''],
    ['genero' => ['nullable', new Enum(GeneroEnum::class)]]
);
echo json_encode($validator->errors()->toArray());
