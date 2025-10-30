### Projeto Web de Anúncios de Veículos (`projeto-web-veiculos`)

Este é o projeto final da disciplina de Programação para Internet (PPI) da **Universidade Federal de Uberlândia (UFU)**, que consiste no desenvolvimento de um portal web especializado em anúncios de veículos.

### 🎯 Objetivo Geral

Desenvolver um portal que permita aos internautas se cadastrarem, efetuarem login e anunciarem seus veículos. O portal deve ser responsivo, seguro (prevenindo ataques como XSS e SQL Injection), e seguir a arquitetura **MVC**.

### ✨ Funcionalidades Principais

O portal possui áreas pública e restrita, com as seguintes funcionalidades:

#### Área Pública (Acesso Livre)

* **Página Principal:** Exibição dos últimos anúncios em formato de *cards* e um painel de buscas para filtrar por marca, modelo e localização.
* **Visualização Detalhada:** Página para ver detalhes do veículo e registrar uma mensagem de interesse.
* **Acesso:** Páginas de Login e Cadastro de novo usuário/anunciante.

#### Área Restrita (Após Login)

* **Criação de Anúncio:** Formulário para cadastrar novos veículos, incluindo marca, modelo, valor, descrição e upload de fotos.
* **Gestão de Anúncios:** Listagem e exclusão de anúncios próprios.
* **Interesses:** Visualização e exclusão das mensagens de interesse deixadas por outros usuários.

### 🛠️ Tecnologias Utilizadas

| Fases do Projeto | Tecnologia | Uso Específico |
| :--- | :--- | :--- |
| **Front-end** | HTML5, CSS3, JavaScript | Estrutura, Estilização (com Flexbox), e manipulação dinâmica da interface (DOM). |
| **Back-end** | **PHP** |Lógica de negócio (Controlador/MVC) e controle de sessão. |
| **Banco de Dados**| **MySQL** | Armazenamento de dados do anunciante, anúncio, fotos e interesses. |
| **Comunicação** | **AJAX (XMLHttpRequest/Fetch)** | Comunicação assíncrona com o back-end, retornando dados no formato **JSON**. O uso de PDO (PHP Data Objects) é obrigatório para a comunicação com o MySQL. |
