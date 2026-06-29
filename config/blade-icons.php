<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Icons Sets
    |--------------------------------------------------------------------------
    */

    'sets' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Default Classes
    |--------------------------------------------------------------------------
    */

    'class' => '',

    /*
    |--------------------------------------------------------------------------
    | Global Default Attributes
    |--------------------------------------------------------------------------
    |
    | Acessibilidade: por padrão, todos os ícones são decorativos e ficam
    | ocultos para leitores de tela (`aria-hidden="true"`). O nome acessível
    | fica no elemento interativo pai (botão/link com `aria-label` ou texto).
    | Para um ícone que precise ser exposto, sobrescreva no call-site com
    | `aria-hidden="false"` e adicione `role="img"` + um rótulo.
    |
    */

    'attributes' => [
        'aria-hidden' => 'true',
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Fallback Icon
    |--------------------------------------------------------------------------
    */

    'fallback' => '',

    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    */

    'components' => [

        'disabled' => false,

        'default' => 'icon',

    ],

];
