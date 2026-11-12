<?php

    include("conexao.php");

    if(strlen($_POST["email"]) == 0){
       header("Location: login.php?error=empty_email");
       exit;
    }

    if(strlen($_POST["senha"]) == 0){
       header("Location: login.php?error=empty_password");
       exit;
    }


    // Buscar dados de entrada
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    // Query com prepared statement
    $sql_code = "SELECT * FROM usuarios WHERE email = ? AND senha = ?";
    $stmt = $pdo->prepare($sql_code);
    $stmt->execute([$email, $senha]);

    // Buscar resultado
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Iniciar sessão se não estiver iniciada
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // Definir variáveis de sessão
        $_SESSION["id"] = $usuario["id"];
        $_SESSION["nome"] = $usuario["nome"];
        
        // Redirecionar para dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }




    /* 
    $email = $mysqli->real_escape_string($_POST["email"]);
    $senha = $mysqli->real_escape_string($_POST["senha"]);

    $sql_code = "SELECT * FROM usuarios WHERE email = '$email' AND senha = '$senha'";

    $sql_query = $mysqli->query($sql_code);
    $quantidade = $sql_query->num_rows;

    if($quantidade == 1){

        $usuario = $sql_query->fetch_assoc();
        
        if(!isset($_SESSION)){
            session_start();
        }
        $_SESSION["id"] = $usuario["id"];
        $_SESSION["nome"] = $usuario["nome"];

        header("Location: dashboard.php");
    }else{
        header("Location: login.php?error=invalid_credentials");
    }
    */

?>