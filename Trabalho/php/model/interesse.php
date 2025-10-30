<?php

class Interesse
{
    // Busca todos os interesses registados para um anúncio específico.
    public static function GetByAnuncio($pdo, $idAnuncio)
    {
        $sql = <<<SQL
            SELECT 
                Id, --  ID da própria mensagem de interesse (para referência futura na exclusão)
                Nome, 
                Telefone, 
                Mensagem, 
                -- Formata a data para um formato mais legível
                DATE_FORMAT(DataHora, '%d/%m/%Y às %H:%i') as DataFormatada
            FROM Interesse
            WHERE IdAnuncio = ?
            ORDER BY DataHora DESC -- Ordena do mais recente para o mais antigo
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idAnuncio]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Verifica se um determinado anúncio pertence ao anunciante logado na sessão
    public static function checkAnuncioOwner($pdo, $idAnuncio, $idAnunciante)
    {
        $sql = "SELECT Id FROM Anuncio WHERE Id = ? AND IdAnunciante = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idAnuncio, $idAnunciante]);
        // Retorna true se encontrar uma correspondência, false caso contrário
        return $stmt->fetch() !== false;
    }

    // Cria um novo registo de interesse para um anúncio.
    public static function Create($pdo, $idAnuncio, $nome, $telefone, $mensagem)
    {
        $sql = <<<SQL
            INSERT INTO Interesse (IdAnuncio, Nome, Telefone, Mensagem, DataHora)
            VALUES (?, ?, ?, ?, NOW())
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idAnuncio, $nome, $telefone, $mensagem]);
        return $pdo->lastInsertId();
    }

    // Apaga um registo de interesse.
    public static function Delete($pdo, $idInteresse, $idAnunciante)
    {
        $sql = <<<SQL
            DELETE i FROM Interesse i 
            INNER JOIN Anuncio a ON i.IdAnuncio = a.Id -- INNER JOIN para garantir que o anunciante só pode apagar mensagens que pertencem aos seus próprios anúncios
            WHERE i.Id = ? AND a.IdAnunciante = ?
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idInteresse, $idAnunciante]);
        
        // Retorna o número de linhas afetadas. Se for > 0, a exclusão foi bem-sucedida.
        return $stmt->rowCount();
    }

}