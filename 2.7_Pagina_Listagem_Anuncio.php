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
    <title>Meus Anúncios</title>
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
        <h2>Meus Anúncios Cadastrados</h2>
        <div class="container" id="anuncios-container">
            <!-- Os cards dos anúncios serão inseridos aqui pelo JS -->
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Sally Motors. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Função para carregar e exibir os anúncios do usuário
        async function carregarAnuncios() {
            const container = document.getElementById('anuncios-container');
            container.innerHTML = '<p>Carregando seus anúncios...</p>';

            try { // pede ao servidor os anúncios do usuário logado 
                const response = await fetch('php/controlador.php?acao=listarMeusAnuncios');
                const result = await response.json();
                
                container.innerHTML = '';  // limpa o container antes de adicionar os cards (mensagem de "carregando")

                if (result.success && result.data.length > 0) {
                    result.data.forEach(anuncio => { // para cada anúncio, cria um card
                        const card = document.createElement('div');  
                        card.className = 'card';
                        card.setAttribute('data-card-id', anuncio.Id); // atributo para identificar o card depois
                        const imgPath = `uploads/${anuncio.FotoPrincipal}`;
                        
                        // Monta o HTML do card com os dados do anúncio e os links/botões de ação
                        card.innerHTML = `
                            <h3>${anuncio.Marca} ${anuncio.Modelo}</h3>
                            <img src="${imgPath}" alt="Foto de ${anuncio.Modelo}" onerror="this.src='images/placeholder.png';">
                            <p class="marca"><strong>Marca:</strong> ${anuncio.Marca}</p>
                            <p class="modelo"><strong>Modelo:</strong> ${anuncio.Modelo}</p>
                            <p class="anodefabricacao"><strong>Ano de Fabricação:</strong> ${anuncio.Ano}</p>
                            <div class="card-actions">
                                <a href="2.8_Pagina_Visualizacao_detalhada.php?id=${anuncio.Id}" class="link-card">Ver Detalhes</a>
                                <a href="2.9_Pagina_de_Listagem_de_Interesses.php?id=${anuncio.Id}" class="link-card">Ver Interesses</a>
                                <button class="deletar" data-id="${anuncio.Id}">Excluir</button>
                            </div>
                        `;
                        container.appendChild(card); // Adiciona o card ao container
                    });
                } else if (result.success && result.data.length === 0) {
                    container.innerHTML = '<p>Você ainda não cadastrou nenhum anúncio.</p>'; // a busca deu certo, mas não há anúncios
                } else {
                    container.innerHTML = `<p>Ocorreu um erro: ${result.message || 'Não foi possível carregar os anúncios.'}</p>`;
                }
            } catch (error) {
                console.error('Erro ao carregar anúncios:', error);
                container.innerHTML = '<p>Não foi possível carregar seus anúncios. Tente novamente mais tarde.</p>';
            }
        }

        // Função para chamar o controlador e excluir um anúncio específico (pelo ID)
        async function excluirAnuncio(idAnuncio) {
            try {
                // Prepara os dados para envio
                const formData = new FormData();
                formData.append('id', idAnuncio);

                // envia a requisição para o controlador
                const response = await fetch('php/controlador.php?acao=excluirAnuncio', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message); // alerta de sucesso

                    // procura na página pelo card que tem o ID do anúncio que foi excluído
                    const cardParaRemover = document.querySelector(`[data-card-id='${idAnuncio}']`);
                    if (cardParaRemover) {
                        cardParaRemover.remove(); // e remove da tela sem recarregar a página
                    }
                } else {
                    alert('Falha ao excluir: ' + result.message);
                }
            } catch (error) {
                console.error('Erro na requisição de exclusão:', error);
                alert('Ocorreu um erro de comunicação ao tentar excluir o anúncio.');
            }
        }

        // Adiciona um único listener de eventos ao container para gerir todos os cliques nos botões
        // quando um clique acontece dentro do container, esta função é ativada
        // 'e.target' diz exatamente qual elemento foi clicado
        // verifica se o elemento clicado é um botão e se ele tem a classe 'deletar'
        document.getElementById('anuncios-container').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('deletar')) {
                const anuncioId = e.target.getAttribute('data-id'); // pega o ID do anúncio que guardado no atributo 'data-id' do botão
                if (confirm('Tem certeza que deseja excluir este anúncio?')) { // pede confirmação antes de excluir
                    excluirAnuncio(anuncioId); // chama a função de exclusão
                }
            }
        });

        // Carrega os anúncios quando a página é totalmente carregada
        document.addEventListener('DOMContentLoaded', carregarAnuncios);
    </script>
</body>
</html>
