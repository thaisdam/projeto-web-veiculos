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
    <title>Lista de Interesses no Anúncio</title>
    <link rel="stylesheet" href="css/style.css">
   
</head>

<body>
    <header>
        <div class="logo-titulo">
            <img src="images/logo.png" alt="logo" width="100" height="100">
            <h1>Sally Motors</h1>
        </div>
        <nav class="header-nav">
            <a href="2.5_Pagina_Principal_Interna.php">Home</a>
            <a href="php/controlador.php?acao=logout">Logout</a>
        </nav>
    </header>

    <main>
        <section class="interesses-container" id="interesses-container">
            <h2>Interesses Registrados neste Anúncio</h2>
            <p>A carregar interesses...</p>
        </section>
        <a href="2.7_Pagina_Listagem_Anuncio.php" class="link-voltar">Voltar para a Listagem</a>
    </main>

    <footer>
        <p>&copy; 2025 Sally Motors. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Função para carregar e exibir as mensagens de interesse
        async function carregarInteresses() {
            const container = document.getElementById('interesses-container');
            const urlParams = new URLSearchParams(window.location.search);
            const idAnuncio = urlParams.get('id'); // Pega o ID do anúncio a partir do URL

            if (!idAnuncio) {
                container.innerHTML = '<h2>Erro</h2><p>ID do anúncio não especificado.</p>';
                return;
            }

            try {
                // Faz a requisição para a nova ação, passando o ID do anúncio
                const response = await fetch(`php/controlador.php?acao=getInteresses&id=${idAnuncio}`);
                const result = await response.json();

                if (result.success) {
                    // Limpa a mensagem de "a carregar"
                    container.innerHTML = '<h2>Interesses Registrados neste Anúncio</h2>';

                    if (result.data.length > 0) {
                        // Para cada mensagem de interesse recebida
                        result.data.forEach(interesse => {
                            const card = document.createElement('div');
                            card.className = 'card';
                            // Monta o HTML do card
                            // Uso da função escapeHTML() para "limpar" os dados que vêm do usuário
                            // Previne ataques de XSS, onde alguém poderia injetar código malicioso na mensagem.
                            card.innerHTML = `
                                <h3>${escapeHTML(interesse.Nome)}</h3>
                                <p><strong>Telefone:</strong> ${escapeHTML(interesse.Telefone)}</p>
                                <p><strong>Mensagem:</strong> ${escapeHTML(interesse.Mensagem)}</p>
                                <p><small>Recebido em: ${interesse.DataFormatada}</small></p>
                                <button class="deletar" onclick="excluirInteresse(${interesse.Id}, this)">Excluir Mensagem</button>
                            `;
                            container.appendChild(card);
                        });
                    } else {
                        container.innerHTML += '<p>Nenhuma mensagem de interesse para este anúncio ainda.</p>';
                    }
                } else {
                    container.innerHTML = `<h2>Erro</h2><p>${result.message}</p>`;
                }
            } catch (error) {
                console.error('Erro ao carregar interesses:', error);
                container.innerHTML = '<h2>Erro</h2><p>Não foi possível carregar as mensagens de interesse.</p>';
            }
        }

        // Função responsável por apagar uma mensagem de interesse específica
        // idInteresse - O ID da mensagem a ser apagada.
        // buttonElement - A referência ao botão que foi clicado.
        async function excluirInteresse(idInteresse, buttonElement) {
            // Pede confirmação antes de apagar
            if (!confirm('Tem a certeza de que deseja apagar esta mensagem?')) {
                return;
            }

            try {
                // Prepara os dados para enviar via POST
                const formData = new URLSearchParams();
                formData.append('id', idInteresse);

                const response = await fetch('php/controlador.php?acao=excluirInteresse', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {                    
                    alert(result.message);
                    // uUsa a referência do botão para encontrar o 'card' pai mais próximo                
                    const card = buttonElement.closest('.card');
                    // Remove o 'card' da mensagem apagada da página sem precisar recarregar
                    card.remove();
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                console.error('Erro ao apagar interesse:', error);
                alert('Ocorreu um erro na comunicação com o servidor.');
            }
        }

        // Função para limpar strings e prevenir XSS
        // Esta função pega uma string e converte quaisquer caracteres de HTML (como <, >, &) em (&lt;, &gt;, &amp;).
        // Isso garante que, um usuário mal-intencionado não consiga injetar código HTML ou JavaScript na página
        // str - string a ser "limpa"
        function escapeHTML(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML; // string segura para ser inserida no HTML
        }

        // Chama a função quando a página é carregada
        document.addEventListener('DOMContentLoaded', carregarInteresses);
    </script>
</body>
</html>