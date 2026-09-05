<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Limite de questões públicas para visitantes
    |--------------------------------------------------------------------------
    |
    | Quantidade de questões distintas que um visitante pode responder antes
    | de precisar criar uma conta ou entrar no Papirar.
    |
    */
    'guest_answer_limit' => (int) env('PUBLIC_GUEST_QUESTION_LIMIT', 5),
];
