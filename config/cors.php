<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', '/login', '/logout', '/user'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://site-madarom-en.vercel.app','https://www.madarom.net','http://localhost:5500', 'http://127.0.0.1:5500', 'http://localhost:3000', 'https://89b12042b796.ngrok-free.app'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
