<?php

require "php/conexaoMysql.php";
require "php/sessionVerification.php"; // Importa a função que verifica se a sessão está ativa

session_start(); // Inicia ou retoma a sessão
exitWhenNotLoggedIn(); // Se o usuário não estiver logado, encerra a execução
$pdo = mysqlConnect();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="css/interna.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <div class="logo-titulo">
            <img src="images/logo.png" alt="logo" width="100" height="100">
            <h1>Sally Motors</h1>
        </div>
        <nav class="header-nav">
        <a href="php/controlador.php?acao=logout">Logout</a>
        </nav>

    </header>
    <main>

        <h1 id="titulo">Bem-vindo à Sally Motors</h1>

        <p id="paragrafo">Selecione uma das opções abaixo para gerenciar seus anúncios e mensagens.</p>

        <div class="button-group">
            <a href="2.6_Pagina_para_Criacao_de_Anuncio.php" class="button">Criar Anúncio</a>
            <a href="2.7_Pagina_Listagem_Anuncio.php" class="button">Listar Anúncios</a>
        </div>

    </main>

    <footer>
        <p>&copy; 2025 Sally Motors. Todos os direitos reservados.</p>
    </footer>
</body>

</html>