<?php

return [
    'unique' => 'O valor informado para o campo :attribute já está em uso.',
    'required' => 'O campo :attribute é obrigatório.',
    'email' => 'O campo :attribute deve conter um endereço de email válido.',
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'confirmed' => 'A confirmação do campo :attribute não confere.',
    
    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmação da senha',
        'current_password' => 'senha atual',
        'first_name' => 'nome',
        'type' => 'tipo de usuário',
        'city' => 'cidade',
        'uf' => 'estado',
        'phone' => 'telefone',
        'price_table_id' => 'tabela de preço',
    ],
    'custom' => [
        'name' => [
            'required' => 'O nome é obrigatório.',
            'unique' => 'Já existe uma tabela de preços com este nome.',
            'max' => 'O nome não pode ter mais de 255 caracteres.',
        ],
        'description' => [
            'max' => 'A descrição não pode ter mais de 255 caracteres.',
        ],
        'ranges' => [
            'required' => 'É necessário adicionar pelo menos uma faixa de preço.',
            'min' => 'É necessário adicionar pelo menos uma faixa de preço.',
            'max_greater_than_min' => 'O valor máximo deve ser maior que o valor mínimo.',
            'overlap' => 'A faixa :range1 se sobrepõe com a faixa :range2.',
        ],
        'ranges.*.min_value' => [
            'required' => 'A quantidade inicial é obrigatória.',
            'integer' => 'A quantidade inicial deve ser um número inteiro.',
            'min' => 'A quantidade inicial deve ser maior ou igual a 0.',
        ],
        'ranges.*.max_value' => [
            'required' => 'A quantidade final é obrigatória.',
            'integer' => 'A quantidade final deve ser um número inteiro.',
            'min' => 'A quantidade final deve ser maior ou igual a 0.',
        ],
        'ranges.*.price' => [
            'required' => 'O valor da etiqueta é obrigatório.',
        ],
        'ranges.*.price_kit' => [
            'required' => 'O valor do kit é obrigatório.',
        ],
    ],
];