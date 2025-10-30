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
    <title>Detalhes do Anúncio</title>
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
        <!-- O conteúdo desta secção será preenchido dinamicamente -->
        <section class="detalhes-anuncio" id="detalhes-container">
            <h2>Detalhes do Anúncio</h2>
            <p>A carregar detalhes...</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Sally Motors. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Esta função é responsável por buscar no servidor todos os detalhes
        // de um anúncio específico e exibi-los na página
        async function carregarDetalhes() {
            const container = document.getElementById('detalhes-container');
            
            // Usamos esta ferramenta para ler o URL da página atual
            // Ele ajuda a extrair informações, nesse caso o ID do anúncio
            const urlParams = new URLSearchParams(window.location.search);
            const idAnuncio = urlParams.get('id');

            // se não houver ID mostra mensagem de erro
            if (!idAnuncio) {
                container.innerHTML = '<h2>Erro</h2><p>ID do anúncio não especificado.</p>';
                return;
            }

            try {
                // Faz a requisição para a nova ação, passando o ID do anúncio
                const response = await fetch(`php/controlador.php?acao=getAnuncioDetalhes&id=${idAnuncio}`);
                const result = await response.json(); // Espera a resposta e converte para JSON

                // se achou o anúncio
                if (result.success) {
                    const anuncio = result.data; // Guarda os dados do anúncio numa variável
                    
                     // prepara uma string vazia para construir o HTML das fotos
                    let fotosHtml = '';
                    anuncio.fotos.forEach(foto => {
                        fotosHtml += `<img src="uploads/${foto}" alt="Foto de ${anuncio.Modelo}" width="200" height="200">`;
                    });

                    // Preenche o container com todos os detalhes do anúncio
                    container.innerHTML = `
                        <h2>${anuncio.Marca} ${anuncio.Modelo}</h2>
                        <div class="anuncio-detalhado">
                            <div class="fotos">
                                ${fotosHtml}
                            </div>
                            <div class="info">
                                <p><strong>Marca:</strong> ${anuncio.Marca}</p>
                                <p><strong>Modelo:</strong> ${anuncio.Modelo}</p>
                                <p><strong>Ano de Fabricação:</strong> ${anuncio.Ano}</p>
                                <p><strong>Cor:</strong> ${anuncio.Cor}</p>
                                <p><strong>Quilometragem:</strong> ${anuncio.Quilometragem} km</p>
                                <p><strong>Descrição:</strong> ${anuncio.Descricao || 'Nenhuma descrição fornecida.'}</p>
                                <p><strong>Valor:</strong> R$ ${parseFloat(anuncio.Valor).toFixed(2)}</p>
                                <p><strong>Estado:</strong> ${anuncio.Estado}</p>
                                <p><strong>Cidade:</strong> ${anuncio.Cidade}</p>
                            </div>
                            <a href="2.7_Pagina_Listagem_Anuncio.php">Voltar para a Listagem</a>
                        </div>
                    `;
                } else {
                    container.innerHTML = `<h2>Erro</h2><p>${result.message}</p>`;
                }
            } catch (error) {
                console.error('Erro ao carregar detalhes:', error);
                container.innerHTML = '<h2>Erro</h2><p>Não foi possível carregar os detalhes do anúncio.</p>';
            }
        }

        // Chama a função quando a página é carregada
        document.addEventListener('DOMContentLoaded', carregarDetalhes);
    </script>
</body>
</html>
