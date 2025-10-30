
<?php

function exitWhenNotLoggedIn()
{ 
  if (!isset($_SESSION['id_anunciante'])) { // Verifica se a variável de sessão 'loggedIn' não está definida
    header("Location: 2.3_Pagina_de_Login.html"); // Se não estiver definida, redireciona o usuário para a página de login
    exit();                         // Interrompe a execução do script 
  }
}
