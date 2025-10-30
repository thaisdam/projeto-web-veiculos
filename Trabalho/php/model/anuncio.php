<?php
class Anuncio
{
     //Cria um novo anúncio e salva suas fotos no banco de dados usando uma transação.
    public static function Create($pdo, $marca, $modelo, $ano, $cor, $quilometragem, $descricao, $valor, $estado, $cidade, $idAnunciante, $fotos)
    {
        // A transação garante que ou tudo é salvo (anúncio e fotos), ou nada é salvo.
        $pdo->beginTransaction(); 
        try {
            // Insere os dados na tabela 'Anuncio'.
            // A coluna DataHora é preenchida automaticamente pelo NOW() do MySQL.
            $sqlAnuncio = <<<SQL
                INSERT INTO Anuncio (Marca, Modelo, Ano, Cor, Quilometragem, Descricao, Valor, Estado, Cidade, IdAnunciante, DataHora)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            SQL;
            
            $stmtAnuncio = $pdo->prepare($sqlAnuncio);

            // Executa a inserção com os parâmetros corretos e na ordem correta
            $stmtAnuncio->execute([
                $marca, $modelo, $ano, $cor, $quilometragem, 
                $descricao, $valor, $estado, $cidade, $idAnunciante
            ]);
            
            // Pega o ID do anúncio para usar na tabela de fotos
            $idAnuncio = $pdo->lastInsertId();

            // Insere os nomes dos arquivos de foto na tabela 'Foto'.
            $sqlFoto = <<<SQL
                INSERT INTO Foto (IdAnuncio, NomeArqFoto) VALUES (?, ?)
            SQL;

            $stmtFoto = $pdo->prepare($sqlFoto);
            foreach ($fotos as $foto) {
                $stmtFoto->execute([$idAnuncio, $foto]); // Insere cada foto associada ao anúncio
            }

            $pdo->commit(); 
            return $idAnuncio;

        } catch (Exception $e) {
            // Se qualquer erro ocorrer, desfaz todas as operações
            $pdo->rollBack(); 
            throw $e; // Propaga a exceção para o controlador poder tratá-la
        }
    }

    // Busca todos os anúncios de um anunciante específico.
    // Para cada anúncio, busca também o nome de uma das fotos para exibição.
    public static function GetByAnunciante($pdo, $idAnunciante)
    {
        $sql = <<<SQL
            SELECT 
                a.Id, 
                a.Marca, 
                a.Modelo, 
                a.Ano,
                -- Subconsulta para buscar o nome de uma foto do anúncio
                -- Para cada anúncio (a.Id), ela vai à tabela de fotos e pega o nome de apenas uma foto.
                (SELECT f.NomeArqFoto 
                 FROM Foto f 
                 WHERE f.IdAnuncio = a.Id 
                 LIMIT 1) AS FotoPrincipal
            FROM Anuncio a
            WHERE a.IdAnunciante = ?
            ORDER BY a.DataHora DESC -- Ordena do mais recente para o mais antigo
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idAnunciante]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Exclui um anúncio, as suas fotos e os interesses associados.
    // Utiliza uma transação para garantir a consistência dos dados.
    // Também verifica se o anúncio pertence ao anunciante logado.
    public static function Delete($pdo, $idAnuncio, $idAnunciante)
    {
        $pdo->beginTransaction(); // transação para garantir que tudo seja apagado ou nada seja apagado
        try {
            // Busca os nomes dos ficheiros de foto antes de apagar os registos
            $sqlSelectFotos = "SELECT NomeArqFoto FROM Foto WHERE IdAnuncio = ?";
            $stmtSelectFotos = $pdo->prepare($sqlSelectFotos);
            $stmtSelectFotos->execute([$idAnuncio]);
            $nomesFotos = $stmtSelectFotos->fetchAll(PDO::FETCH_COLUMN);

            // Exclui os interesses associados ao anúncio
            $sqlDeleteInteresses = "DELETE FROM Interesse WHERE IdAnuncio = ?";
            $stmtDeleteInteresses = $pdo->prepare($sqlDeleteInteresses);
            $stmtDeleteInteresses->execute([$idAnuncio]);

            // Exclui os registos das fotos da tabela 'Foto'
            $sqlDeleteFotos = "DELETE FROM Foto WHERE IdAnuncio = ?";
            $stmtDeleteFotos = $pdo->prepare($sqlDeleteFotos);
            $stmtDeleteFotos->execute([$idAnuncio]);

            // Exclui o anúncio da tabela 'Anuncio'
            // A cláusula WHERE garante que um usuário só pode apagar os seus próprios anúncios
            $sqlDeleteAnuncio = "DELETE FROM Anuncio WHERE Id = ? AND IdAnunciante = ?";
            $stmtDeleteAnuncio = $pdo->prepare($sqlDeleteAnuncio);
            $stmtDeleteAnuncio->execute([$idAnuncio, $idAnunciante]);

            // Se a exclusão do anúncio não afetou nenhuma linha (rowCount === 0), significa que não pertence ao utilizador
            if ($stmtDeleteAnuncio->rowCount() === 0) {
                throw new Exception('O anúncio não foi encontrado ou não tem permissão para o excluir.');
            }

            // Apaga os ficheiros de imagem do servidor
            $uploadDir = '../uploads/';
            foreach ($nomesFotos as $nomeFoto) {
                $caminhoCompleto = $uploadDir . $nomeFoto;
                if (file_exists($caminhoCompleto)) {
                    unlink($caminhoCompleto);
                }
            }

            // Se tudo correu bem, confirma a transação
            $pdo->commit();
            return true;

        } catch (Exception $e) {
            // Se ocorreu algum erro, desfaz todas as operações
            $pdo->rollBack();
            throw $e;
        }
    }

    // Busca todos os detalhes de um anúncio específico pelo seu ID.
    // Retorna os dados do anúncio e um array com os nomes de todas as suas fotos.
    public static function GetById($pdo, $idAnuncio)
    {
        $sqlAnuncio = <<<SQL
            SELECT *
            FROM Anuncio
            WHERE Id = ?
        SQL;

        $stmtAnuncio = $pdo->prepare($sqlAnuncio);
        $stmtAnuncio->execute([$idAnuncio]);
        $anuncio = $stmtAnuncio->fetch(PDO::FETCH_OBJ);

        // Se o anúncio não for encontrado, retorna null
        if (!$anuncio) {
            return null;
        }

        // Busca todas as fotos associadas ao anúncio
        $sqlFotos = <<<SQL
            SELECT NomeArqFoto
            FROM Foto
            WHERE IdAnuncio = ?
        SQL;

        $stmtFotos = $pdo->prepare($sqlFotos); 
        $stmtFotos->execute([$idAnuncio]);
        
        // Adiciona a lista de fotos como uma nova propriedade do objeto anúncio
        $anuncio->fotos = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);

        return $anuncio;
    }

    // Busca anúncios com base nos filtros fornecidos.
    public static function buscarAnuncios($pdo, $marca, $modelo, $cidade)
    {
        $sql = <<<SQL
            SELECT 
                a.Id, a.Marca, a.Modelo, a.Ano, a.Cidade, a.Valor,
                (SELECT f.NomeArqFoto FROM Foto f WHERE f.IdAnuncio = a.Id LIMIT 1) as FotoPrincipal
            FROM Anuncio a
        SQL;

        $params = [];
        $whereClauses = [];

        // Adiciona filtros dinamicamente se eles forem fornecidos
        if ($marca) {
            $whereClauses[] = "a.Marca = ?";
            $params[] = $marca;
        }
        if ($modelo) {
            $whereClauses[] = "a.Modelo = ?";
            $params[] = $modelo;
        }
        if ($cidade) {
            $whereClauses[] = "a.Cidade = ?";
            $params[] = $cidade;
        }

         // Se houver pelo menos uma cláusula 'where', junta todas com 'AND'
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $sql .= " ORDER BY a.DataHora DESC LIMIT 20";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Filtros: 
    // As próximas funções buscam dados para preencher os menus <select> da página inicial.

    // Retorna todas as marcas únicas com anúncios cadastrados.
    public static function getMarcas($pdo)
    {
        $sql = "SELECT DISTINCT Marca FROM Anuncio ORDER BY Marca ASC"; // 'DISTINCT' garante que cada marca apareça apenas uma vez na lista.
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Retorna todos os modelos de uma marca específica (sem duplicados).
    public static function getModelos($pdo, $marca)
    {
        $sql = "SELECT DISTINCT Modelo FROM Anuncio WHERE Marca = ? ORDER BY Modelo ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$marca]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Retorna todas as cidades para uma marca e modelo específicos (sem duplicados).
    public static function getCidades($pdo, $marca, $modelo)
    {
        $sql = "SELECT DISTINCT Cidade FROM Anuncio WHERE Marca = ? AND Modelo = ? ORDER BY Cidade ASC"; 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$marca, $modelo]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    }
    ?>