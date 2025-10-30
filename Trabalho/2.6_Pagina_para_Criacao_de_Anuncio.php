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
    <title>Página de Criação de Anúncio | Anunciante</title>
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <div class="logo-titulo"> <img src="images/logo.png" alt="logo" width="100" height="100">
            <h1>Sally Motors</h1>
        </div>
        <nav class="header-nav">
            <a href="2.5_Pagina_Principal_Interna.php">Home</a>
            <a href="php/controlador.php?acao=logout">Logout</a>
        </nav>
    </header>
    
    <main>
        <div>
            <form id="form-anuncio" enctype="multipart/form-data">
                <div>
                    <h1>Criação de Anúncio</h1>
                </div>

                <div>
                    <label for="marca">Marca</label>
                    <input type="text" id="marca" name="marca" required>
                </div>

                <div>
                    <label for="modelo">Modelo</label>
                    <input type="text" id="modelo" name="modelo" required>
                </div>

                <div>
                    <label for="ano">Ano de Fabricação</label>
                    <input type="number" id="ano" name="ano" min="1900" max="2025" required>
                </div>

                <div>
                    <label for="cor">Cor</label>
                    <input type="text" id="cor" name="cor" required>
                </div>

                <div>
                    <label for="quilometragem">Quilometragem (KM)</label>
                    <input type="number" id="quilometragem" name="quilometragem" required>
                </div>

                <div>
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="5"></textarea>
                </div>

                <div>
                    <label for="valor">Valor</label>
                    <input type="number" id="valor" name="valor" required>
                </div>

                <div>
                    <label for="estado">Estado:</label>
                    <select name="estado" id="estado" required>
                        <option value="">Selecione</option>
                        <option value="MG">Minas Gerais</option>
                        <option value="SP">São Paulo</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="DF">Distrito Federal</option>
                        <option value="ES">Espírito Santo</option>
                        <option value="GO">Goiás</option>
                    </select>
                </div>

                <div>
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" required>
                </div>

                <div>
                    <label for="fotos">Fotos (envie/ selecione as fotos juntas)</label>
                    <input type="file" id="fotos" name="fotos[]" multiple accept="image/*" required>
                </div>

                <div>
                    <button type="submit">Cadastrar Anúncio</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div>
            <p>&copy; 2025 Sally Motors. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- SCRIPT PARA ENVIO DO FORMULÁRIO COM AJAX -->
    <script>
    document.getElementById('form-anuncio').addEventListener('submit', async function(e) {
        e.preventDefault();

        const fotosInput = document.getElementById('fotos');
        if (fotosInput.files.length < 3) { // Se o número de arquivos selecionados for menor que 3
            alert('Por favor, selecione pelo menos 3 fotos do veículo.'); // mensagem de alerta
            return; // Impede o envio
        }

        const form = e.target;
        const formData = new FormData(form);

        try { // Envia a requisição para o controlador PHP
            const response = await fetch('php/controlador.php?acao=criarAnuncio', { 
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            // Verifica se a resposta indica sucesso ou falha
            if (result.success) { // Mostra a mensagem de sucesso 
                alert(result.message);
                // Redireciona para a página de listagem de anúncios após o sucesso
                window.location.href = '2.7_Pagina_Listagem_Anuncio.php';
            } else {
                alert('Falha ao criar anúncio: ' + result.message);
            }

        } catch (error) {
            console.error('Erro na requisição:', error);
            alert('Ocorreu um erro de comunicação com o servidor.');
        }
    });
    </script>
</body>
</html>
