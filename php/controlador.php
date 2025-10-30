<?php

session_start();

require "conexaoMysql.php";
require "model/anunciante.php";
require "model/anuncio.php";
require "model/interesse.php";

header('Content-Type: application/json; charset=utf-8'); // Define o cabeçalho da resposta: em formato JSON
$pdo = mysqlConnect();

// Pega a 'acao' que foi enviada no URL (via GET). Se não houver nenhuma, define-a como uma string vazia.
// É esta variável que decide qual parte do código será executada.
$acao = $_GET['acao'] ?? '';

// Função para validar a foto enviada
function validaFoto($arquivo)
{
    // Verifica se o arquivo foi realmente enviado
    if (!is_uploaded_file($arquivo['tmp_name'])) {
        throw new Exception("Falha ao carregar o arquivo de imagem.");
    }

    // Pega informações da imagem e verifica se é uma imagem válida
    list($width, $height, $type) = getimagesize($arquivo['tmp_name']);
    if (empty($width) || empty($height)) {
        throw new Exception("O arquivo '{$arquivo['name']}' não corresponde a uma imagem válida.");
    }

    // Verifica o tipo de arquivo (MIME type)
    $imageType = image_type_to_mime_type($type);
    if ($imageType != "image/jpeg" && $imageType != "image/png") {
        throw new Exception("A foto '{$arquivo['name']}' deve estar no formato JPEG ou PNG.");
    }

    return $imageType;
}

// O 'switch' direciona a requisição para o local certo com base na 'acao'
switch ($acao) {

    // Ações de Anunciante
    case 'cadastrarAnunciante':
        $nome = $_POST["nome"] ?? "";
        $cpf = $_POST["cpf"] ?? "";
        $email = $_POST["email"] ?? "";
        $senha = $_POST["senha"] ?? "";
        $telefone = $_POST["telefone"] ?? "";
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $response = new stdClass(); // Prepara um objeto de resposta vazio.
        try {
            // Chama o "Construtor de Contas" da classe Anunciante para salvar os dados.
            Anunciante::Create($pdo, $nome, $cpf, $email, $senhaHash, $telefone);
            $response->success = true; // Indica que a operação foi bem-sucedida.
            $response->message = 'Cadastro realizado com sucesso!'; // Mensagem de sucesso.
        } catch (Exception $e) {
            $response->success = false; // Indica que houve uma falha na operação.
            $response->message = 'Erro ao cadastrar: ' . $e->getMessage(); // Mensagem de erro com detalhes.
        } 
        echo json_encode($response); // Envia a resposta de volta para o JavaScript em formato JSON.
        break;

    case 'login':
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $response = new stdClass();
        try {
            $anunciante = Anunciante::GetByEmail($pdo, $email);
            if ($anunciante && password_verify($senha, $anunciante->SenhaHash)) { // Verifica se a senha bate
                // Armazena os dados do anunciante na sessão para manter o login
                $_SESSION['id_anunciante'] = $anunciante->Id;
                $_SESSION['nome_anunciante'] = $anunciante->Nome;
                $response->success = true;
            } else {
                $response->success = false;
                $response->message = 'E-mail ou senha inválidos';
            }
        } catch (Exception $e) { 
            $response->success = false;
            $response->message = 'Ocorreu uma falha: ' . $e->getMessage();
        }
        echo json_encode($response);
        break;

    case 'logout':
        
        // inicia a sessão
        session_start(); // Cria ou resume uma sessão existente para poder manipulá-la

        // apaga as variáveis de sessão de $_SESSION
        session_unset(); // Remove todos os dados armazenados na variável global $_SESSION

        // destrói a sessão e as variáveis fisicamente (em arquivo)
        session_destroy(); // Finaliza a sessão no servidor e apaga o arquivo de sessão

        // exclui o cookie da sessão no computador do usuário
        setcookie(session_name(), "", 1, "/"); // Cria um cookie vazio com data de expiração no passado para apagar o cookie de sessão

        // redireciona o usuário para a página de login
        header('Location: ../2.3_Pagina_de_Login.html'); // Envia um cabeçalho HTTP para redirecionar o navegador para a página de login
        exit(); // Interrompe imediatamente a execução do script para garantir o redirecionamento
        break;


    // Ações de Anúncio (Área Restrita)
    case 'criarAnuncio':  
        // Verifica se o utilizador está logado antes de continuar
        if (!isset($_SESSION['id_anunciante'])) { 
            header('HTTP/1.1 401 Unauthorized'); // Envia um código de erro "Não Autorizado".
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit();
        }

        // Verifica a quantidade de fotos
        if (!isset($_FILES['fotos']) || count($_FILES['fotos']['name']) < 3) {
            echo json_encode(['success' => false, 'message' => 'Pelo menos três fotos são necessárias.']);
            exit();
        }
        
        $response = new stdClass();
        $novosNomesFotos = []; // Array para guardar os novos nomes das fotos salvas.
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) { // Se a pasta 'uploads' não existir, cria
            mkdir($uploadDir, 0777, true);
        }

        try {
            // Itera sobre cada arquivo enviado
            for ($i = 0; $i < count($_FILES['fotos']['name']); $i++) {
                
                // Cria um array associativo para cada arquivo para passar para a função de validação
                $arquivo = [
                    'name' => $_FILES['fotos']['name'][$i],
                    'type' => $_FILES['fotos']['type'][$i],
                    'tmp_name' => $_FILES['fotos']['tmp_name'][$i],
                    'error' => $_FILES['fotos']['error'][$i],
                    'size' => $_FILES['fotos']['size'][$i]
                ];

                if ($arquivo['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Erro no upload do arquivo: ' . $arquivo['name']);
                }

                // Valida a foto
                $tipoArquivoImagem = validaFoto($arquivo);
                
                // Renomeia o arquivo para evitar colisões de nome
                $extensao = substr($tipoArquivoImagem, 6); // Extrai 'jpeg' ou 'png'
                $novoNome = uniqid('img_', true) . '.' . $extensao;

                // Move o arquivo validado para a pasta de uploads
                if (move_uploaded_file($arquivo['tmp_name'], $uploadDir . $novoNome)) {
                    $novosNomesFotos[] = $novoNome;
                } else {
                    throw new Exception('Não foi possível mover o arquivo: ' . $arquivo['name']);
                }
            }

            // Pega os dados do formulário
            $idAnunciante = $_SESSION['id_anunciante'];
            $marca = $_POST['marca'] ?? '';
            $modelo = $_POST['modelo'] ?? '';
            $ano = $_POST['ano'] ?? '';
            $cor = $_POST['cor'] ?? '';
            $quilometragem = $_POST['quilometragem'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $valor = $_POST['valor'] ?? '';
            $estado = $_POST['estado'] ?? '';
            $cidade = $_POST['cidade'] ?? '';

            // Insere no banco de dados
            Anuncio::Create($pdo, $marca, $modelo, $ano, $cor, $quilometragem, $descricao, $valor, $estado, $cidade, $idAnunciante, $novosNomesFotos);
            
            $response->success = true;
            $response->message = 'Anúncio criado com sucesso!';

        } catch (Exception $e) {
            // Se der erro, apaga as fotos que já foram salvas para não deixar lixo no servidor
            foreach ($novosNomesFotos as $foto) {
                unlink($uploadDir . $foto);
            }
            $response->success = false;
            $response->message = 'Erro ao criar o anúncio: ' . $e->getMessage();
        }
        
        echo json_encode($response);
        break;

    case 'listarMeusAnuncios':
        if (!isset($_SESSION['id_anunciante'])) { // só usuários logados podem listar seus anúncios
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit();
        }
        try {
            $anuncios = Anuncio::GetByAnunciante($pdo, $_SESSION['id_anunciante']);
            echo json_encode(['success' => true, 'data' => $anuncios]);
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'excluirAnuncio':
        if (!isset($_SESSION['id_anunciante'])) { 
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit(); 
        }
        $idAnuncio = $_POST['id'] ?? null;
        $response = new stdClass();
        try {
            // passa o ID do anúncio e o ID do utilizador logado.
            // A classe Anuncio vai usar ambos para garantir a permissão
            Anuncio::Delete($pdo, $idAnuncio, $_SESSION['id_anunciante']);
            $response->success = true;
            $response->message = 'Anúncio excluído com sucesso!';
        } catch (Exception $e) {
            $response->success = false;
            $response->message = 'Erro ao excluir o anúncio: ' . $e->getMessage();
        }
        echo json_encode($response);
        break;

    case 'getAnuncioDetalhes':
        // Esta ação é pública, não precisa de verificação de login
        $idAnuncio = $_GET['id'] ?? null;
        try {
            $anuncio = Anuncio::GetById($pdo, $idAnuncio);
            echo json_encode(['success' => true, 'data' => $anuncio]);
        } catch (Exception $e) { 
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar detalhes do anúncio: ' . $e->getMessage()]);
         }
        break;
        
    // Ações de Interesse
    // página pública, qualquer pessoa pode registrar interesse
    case 'registrarInteresse':
        $idAnuncio = $_POST['idAnuncio'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $mensagem = $_POST['mensagem'] ?? '';
        $response = new stdClass();
        try {
            Interesse::Create($pdo, $idAnuncio, $nome, $telefone, $mensagem);
            $response->success = true;
            $response->message = 'Interesse registrado com sucesso!';
        } catch (Exception $e) {
            $response->success = false;
            $response->message = 'Erro ao registrar interesse: ' . $e->getMessage();
         }
        echo json_encode($response);
        break;

    case 'getInteresses':
        if (!isset($_SESSION['id_anunciante'])) {  // só o anunciante dono do anúncio pode ver os interesses quando logado
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit(); 
        }
        $idAnuncio = $_GET['id'] ?? null;
        try {
            // Além de estar logado, verifica se o anúncio realmente pertence a este usuário
            if (!Interesse::checkAnuncioOwner($pdo, $idAnuncio, $_SESSION['id_anunciante'])) {
                throw new Exception('Permissão negada.');
            }
            $interesses = Interesse::GetByAnuncio($pdo, $idAnuncio);
            echo json_encode(['success' => true, 'data' => $interesses]);
        } catch (Exception $e) { 
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar interesses: ' . $e->getMessage()]);
         }
        break;

    case 'excluirInteresse':
        if (!isset($_SESSION['id_anunciante'])) { 
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit(); 
        }
        $idInteresse = $_POST['id'] ?? null;
        $response = new stdClass();
        try {
             // A própria classe Interesse fará a verificação de permissão.
            $linhasAfetadas = Interesse::Delete($pdo, $idInteresse, $_SESSION['id_anunciante']);
            if ($linhasAfetadas > 0) {
                $response->success = true;
                $response->message = 'Mensagem de interesse excluída!';
            } else {
                $response->success = false;
                $response->message = 'Não foi possível excluir.';
            }
        } catch (Exception $e) { 
            $response->success = false;
            $response->message = 'Erro ao excluir interesse: ' . $e->getMessage();
         }
        echo json_encode($response);
        break;

    // Ações para Filtros da Página Principal Externa
    case 'getMarcas':
        try {
            echo json_encode(Anuncio::getMarcas($pdo));
        } catch (Exception $e) { 
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar as marcas: ' . $e->getMessage()]);
         }
        break;

    case 'getModelos':
        $marca = $_GET['marca'] ?? '';
        try {
            echo json_encode(Anuncio::getModelos($pdo, $marca));
        } catch (Exception $e) { 
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar os modelos: ' . $e->getMessage()]);
         }
        break;

    case 'getCidades':
        $marca = $_GET['marca'] ?? '';
        $modelo = $_GET['modelo'] ?? '';
        try {
            echo json_encode(Anuncio::getCidades($pdo, $marca, $modelo));
        } catch (Exception $e) { 
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar as cidades: ' . $e->getMessage()]);
         }
        break;

    case 'buscarAnuncios':
        $marca = $_GET['marca'] ?? '';
        $modelo = $_GET['modelo'] ?? '';
        $cidade = $_GET['cidade'] ?? '';
        try {
            echo json_encode(Anuncio::buscarAnuncios($pdo, $marca, $modelo, $cidade));
        } catch (Exception $e) { 
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar os anúncios: ' . $e->getMessage()]);
         }
        break;

    default:
    // Se a 'acao' não corresponder a nenhuma das opções acima, retorna um erro.
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Ação não disponível']);
        exit();
}
?>