<?php
class Anunciante
{
  // Método para criar um novo anunciante
  public static function Create($pdo, $nome, $cpf, $email, $senhaHash, $telefone)
  {
    // Prevenção contra SQL Injection usando prepared statements
    $sql = <<<SQL
      INSERT INTO Anunciante (Nome, CPF, Email, SenhaHash, Telefone)
      VALUES (?, ?, ?, ?, ?)
    SQL;
    
    $stmt = $pdo->prepare($sql); // Prepara a consulta
    $stmt->execute([$nome, $cpf, $email, $senhaHash, $telefone]); //Executa o comando, enviando os dados do usuário num pacote separado.
    //    O banco de dados "preenche os campos em branco" do formulário de forma segura.
    //    Os dados são tratados apenas como texto, e nunca como parte do comando SQL.
    //    Isso impede que um hacker insira comandos maliciosos nos campos do formulário.

    return $pdo->lastInsertId(); //// Após a inserção, esta função do PDO retorna o ID da linha que acabamos de criar.
  }
  // Método para buscar um anunciante pelo email
public static function GetByEmail($pdo, $email)
{
  $sql = <<<SQL
    SELECT SenhaHash, Id, Nome
    FROM Anunciante
    WHERE Email = ?
    SQL;

  $stmt = $pdo->prepare($sql); 
  $stmt->execute([$email]);
  return $stmt->fetch(PDO::FETCH_OBJ); // Retorna um objeto com os dados do anunciante ou false se não encontrado
}
}
?>