<?php
//Controller da entidade de pessoas.
//Em uma arquitetura MVC, ele recebe a requisição, valida dados e acessa o banco.
class PessoasController
{
    //Conexão PDO reutilizada em todos os métodos.
    private PDO $pdo;

    public function __construct()
    {
        //Importa o arquivo que inicializa o projeto $pdo.
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        //Define a saída em JSON para APIs/consumo por front-end.
        header('Content-Type: application/json; charset=utf-8');

        //Consulta todos os pessoas com ordenação decrescente por ID.
        $sql = 'SELECT id, nome, cpf, telefone, data_nascimento, criado_em
                FROM pessoas
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //JSON_PRETTY_PRINT melhora leitura em desenvolvimento.
        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void 
    {
        header ('Content-Type: application/json; charset=utf-8');

        //Lê e valida o ID recebido por GET.
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        //Consulta parametrizada evita SQL Injection.
        $sql = 'SELECT id, nome, cpf, telefone, data_nascimento, criado_em
                FROM pessoas
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoas = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoas) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        //Coleta dados do formulário (POST).
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = $_POST['telefone'] ?? '';
        $data_nascimento = $_POST['data_nascimento'] ?? '';

        //Regras mínimas de validação de entrada.
        if ($nome === '' || $cpf === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e cpf sao obrigatorios.']);
            return;
        }

        if (strlen($cpf) < 11) {
            http_response_code(400);
            echo json_encode(['erro' => 'CPF inválido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO pessoas (nome, cpf, telefone, data_nascimento)
                    VALUES (:nome, :cpf, :telefone, :data_nascimento)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':cpf', $cpf);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':data_nascimento', $data_nascimento);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // ID vem no POST para operação de update.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = $_POST['telefone'] ?? '';
        $data_nascimento = $_POST['data_nascimento'] ?? '';

        if (!$id || $nome === '' || $cpf === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome e cpf sao obrigatorios.']);
            return;
        }

        if (strlen($cpf) < 11) {
            http_response_code(400);
            echo json_encode(['erro' => 'CPF inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE pessoas
                    SET nome = :nome,
                        cpf = :cpf,
                        telefone = :telefone,
                        data_nascimento = :data_nascimento
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':cpf', $cpf);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':data_nascimento', $data_nascimento);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Exclusão por ID recebido no corpo da requisição.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = 'DELETE FROM pessoas WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa excluída com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir pessoa.']);
        }
    }
}