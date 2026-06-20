<?php

class PessoasController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = 'SELECT id, nome, documento, telefone, email, curso, periodo, 
                        observacoes, status, criado_em, atualizado_em
                FROM pessoas
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT id, nome, documento, telefone, email, curso, periodo, 
                        observacoes, status, criado_em, atualizado_em
                FROM pessoas
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if ($nome === '' || $documento === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome, documento e email são obrigatórios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Email inválido.']);
            return;
        }

        if (strlen($documento) < 11) {
            http_response_code(400);
            echo json_encode(['erro' => 'Documento deve ter pelo menos 11 caracteres.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Permitidos: ativo, inativo']);
            return;
        }

        try {
            $checkSql = 'SELECT id FROM pessoas WHERE documento = :documento LIMIT 1';
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindValue(':documento', $documento);
            $checkStmt->execute();

            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                http_response_code(409);
                echo json_encode(['erro' => 'Documento já cadastrado.']);
                return;
            }

            $checkEmailSql = 'SELECT id FROM pessoas WHERE email = :email LIMIT 1';
            $checkEmailStmt = $this->pdo->prepare($checkEmailSql);
            $checkEmailStmt->bindValue(':email', $email);
            $checkEmailStmt->execute();

            if ($checkEmailStmt->fetch(PDO::FETCH_ASSOC)) {
                http_response_code(409);
                echo json_encode(['erro' => 'Email já cadastrado.']);
                return;
            }

            $sql = 'INSERT INTO pessoas 
                    (nome, documento, email, telefone, curso, periodo, observacoes, status)
                    VALUES 
                    (:nome, :documento, :email, :telefone, :curso, :periodo, :observacoes, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':telefone', $telefone ?: null);
            $stmt->bindValue(':curso', $curso ?: null);
            $stmt->bindValue(':periodo', $periodo ?: null);
            $stmt->bindValue(':observacoes', $observacoes ?: null);
            $stmt->bindValue(':status', $status);
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

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $documento === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome, documento e email são obrigatórios.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Email inválido.']);
            return;
        }

        if (strlen($documento) < 11) {
            http_response_code(400);
            echo json_encode(['erro' => 'Documento deve ter pelo menos 11 caracteres.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Permitidos: ativo, inativo']);
            return;
        }

        try {
            $checkSql = 'SELECT id FROM pessoas WHERE documento = :documento AND id != :id LIMIT 1';
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindValue(':documento', $documento);
            $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                http_response_code(409);
                echo json_encode(['erro' => 'Documento já cadastrado para outra pessoa.']);
                return;
            }

            $checkEmailSql = 'SELECT id FROM pessoas WHERE email = :email AND id != :id LIMIT 1';
            $checkEmailStmt = $this->pdo->prepare($checkEmailSql);
            $checkEmailStmt->bindValue(':email', $email);
            $checkEmailStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $checkEmailStmt->execute();

            if ($checkEmailStmt->fetch(PDO::FETCH_ASSOC)) {
                http_response_code(409);
                echo json_encode(['erro' => 'Email já cadastrado para outra pessoa.']);
                return;
            }

            $sql = 'UPDATE pessoas
                    SET nome = :nome,
                        documento = :documento,
                        email = :email,
                        telefone = :telefone,
                        curso = :curso,
                        periodo = :periodo,
                        observacoes = :observacoes,
                        status = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':telefone', $telefone ?: null);
            $stmt->bindValue(':curso', $curso ?: null);
            $stmt->bindValue(':periodo', $periodo ?: null);
            $stmt->bindValue(':observacoes', $observacoes ?: null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['erro' => 'Pessoa não encontrada.']);
                return;
            }

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $checkSql = 'SELECT id FROM pessoas WHERE id = :id LIMIT 1';
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch(PDO::FETCH_ASSOC)) {
                http_response_code(404);
                echo json_encode(['erro' => 'Pessoa não encontrada.']);
                return;
            }

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

    public function inativar(): void
{
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE pessoas SET status = :status WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', 'inativo');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['erro' => 'Pessoa não encontrada.']);
                return;
            }

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}