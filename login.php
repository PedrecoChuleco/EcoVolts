<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email"
                    id="email"
                    name="email"
                    placeholder="Digite seu email"
                    required      
                >
            </div>

            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    required            
                >
            </div>

            <button type="submit" class="login-btn">Entrar</button>
        </form>

        <?php 
            if(isset($_GET['error'])){
                echo '<div class="error-message">';
                switch($_GET['error']){
                    case "invalid_credentials":
                        echo "Email ou senha não encontrados!";
                        break;
                    case "empty_email":
                        echo "Informe um email!";
                        break;
                    case "empty_password":
                        echo "Informe uma senha!";
                        break;
                }
                echo '</div>';
            }
        ?>

        <div class="form-footer">
            <a href="#">Esqueceu sua senha?</a>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>