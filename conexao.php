<?php

    $usuario  = "root";
    $senha    = "";
    $database = "bookscoffee";
    $host     = "127.0.0.1";
    $porta    = 3307;

    // mysqli
    try {
        $mysqli = new mysqli($host, $usuario, $senha, $database, $porta);
        $mysqli->set_charset("utf8");
    } catch (mysqli_sql_exception $e) {
        die("Falha ao conectar ao banco de dados: " . $e->getMessage());
    }

    // PDO
    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$porta;dbname=$database;charset=utf8",
            $usuario,
            $senha
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }

?>