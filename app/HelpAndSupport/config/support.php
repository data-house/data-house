<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Support email
    |--------------------------------------------------------------------------
    |
    | This value controls the link to create a support request. By default
    | the link is shown if a support email is given.
    |
    */

    'email' => env('SUPPORT_TICKET_EMAIL', null),

    /*
    |--------------------------------------------------------------------------
    | Support Help Pages
    |--------------------------------------------------------------------------
    |
    | This value controls the help pages. 
    | If a link is given the help menu will open that URL.
    |
    */

    'help' => env('SUPPORT_PAGE_URL', null),

];
