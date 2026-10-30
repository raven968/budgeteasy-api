<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | ISO 4217 codes a budget can use. Amounts are stored in the currency's
    | minor unit (cents).
    |
    */

    'currencies' => ['MXN', 'USD', 'EUR', 'CAD', 'GBP', 'COP', 'ARS', 'CLP', 'PEN'],

    /*
    |--------------------------------------------------------------------------
    | Maximum Amount
    |--------------------------------------------------------------------------
    |
    | Upper bound, in cents, for a budget or expense amount.
    |
    */

    'max_amount' => 99_999_999_999,

];
