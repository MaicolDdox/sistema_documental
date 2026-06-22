<?php
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;
use App\Enums\GeneroEnum;

$validator = Validator::make(
    ['genero' => ''],
    ['genero' => ['nullable', new Enum(GeneroEnum::class)]]
);
echo json_encode($validator->errors()->toArray());
