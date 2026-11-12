<?php
/**
 * Database credentials.
 *
 * Keep this file OUT of version control (see .gitignore) and out of any
 * publicly-served directory in production. On shared hosting, everything
 * under your project root is usually reachable by URL unless you
 * explicitly block .php files that shouldn't be — so also see the
 * "Deny direct access" note in MYSQL-SETUP.md.
 */
return [
    'host'    => '127.0.0.1',
    'port'    => 3307,
    'name'    => 'ecovolts',
    'user'    => 'ecovolts_app',
    'pass'    => 'segredo',
    'charset' => 'utf8mb4',

    // Em DESENVOLVIMENTO, coloque true para ver o erro real da conexão na tela
    // (senha errada, banco inexistente, driver ausente...). Em produção: false.
    'debug'   => false,
];
