<?php
class AtendimentosController
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

        $sql = 'SELECT ta.id_atendimento, ta.data_atendimento, ta.descricao_atendimento,
                       p.nome AS pessoa_nome,
                       a.titulo AS tipo_atendimento_titulo
                FROM tipoatendimentos ta
                INNER JOIN pessoas p ON ta.pessoa_id = p.id_pessoa
                INNER JOIN atendimento a ON ta.tipo_atendimento_id = a.id_tipos_atendimentos
                ORDER BY ta.id_atendimento DESC';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $pessoa_id = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipo_atendimento_id = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $descricao_atendimento = trim($_POST['descricao_atendimento'] ?? '');

        if (!$usuario_id || !$pessoa_id || !$tipo_atendimento_id) {
            http_response_code(400);
            echo json_encode(['erro' => 'usuario_id, pessoa_id e tipo_atendimento_id são obrigatórios.']);
            return;
        }

        try {
            $sql = 'INSERT INTO tipoatendimentos (usuario_id, pessoa_id, tipo_atendimento_id, descricao_atendimento)
                    VALUES (:usuario_id, :pessoa_id, :tipo_atendimento_id, :descricao_atendimento)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipo_atendimento_id, PDO::PARAM_INT);
            $stmt->bindValue(':descricao_atendimento', $descricao_atendimento);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento cadastrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar atendimento.']);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id_atendimento', FILTER_VALIDATE_INT);
        $descricao = trim($_POST['descricao_atendimento'] ?? '');

        if (!$id || $descricao === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e descrição do atendimento são obrigatórios.']);
            return;
        }

        try {
            $sql = 'UPDATE tipoatendimentos SET descricao_atendimento = :descricao WHERE id_atendimento = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar atendimento.']);
        }
    }

    public function visualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT ta.id_atendimento, ta.data_atendimento, ta.descricao_atendimento, ta.usuario_id,
                       p.nome AS pessoa_nome, p.cpf AS pessoa_cpf,
                       a.titulo AS tipo_atendimento_titulo, a.descricao AS tipo_atendimento_descricao
                FROM tipoatendimentos ta
                INNER JOIN pessoas p ON ta.pessoa_id = p.id_pessoa
                INNER JOIN atendimento a ON ta.tipo_atendimento_id = a.id_tipos_atendimentos
                WHERE ta.id_atendimento = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

?>