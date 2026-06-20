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

        $sql = 'SELECT 
                    a.id,
                    a.data_atendimento,
                    a.descricao,
                    a.status,
                    p.nome AS pessoa_nome,
                    p.documento AS pessoa_documento,
                    ta.nome AS tipo_atendimento_nome,
                    u.nome AS usuario_nome
                FROM atendimentos a
                INNER JOIN pessoas p ON a.pessoa_id = p.id
                INNER JOIN tipos_atendimentos ta ON a.tipo_atendimento_id = ta.id
                INNER JOIN usuarios u ON a.usuario_id = u.id
                ORDER BY a.id DESC';

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
        $descricao = trim($_POST['descricao'] ?? '');
        $data_atendimento = $_POST['data_atendimento'] ?? date('Y-m-d');
        $horario_atendimento = $_POST['horario_atendimento'] ?? date('H:i:s');

        if (!$usuario_id || !$pessoa_id || !$tipo_atendimento_id || $descricao === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'usuario_id, pessoa_id, tipo_atendimento_id e descricao são obrigatórios.']);
            return;
        }

        try {
            $sql = 'INSERT INTO atendimentos 
                    (usuario_id, pessoa_id, tipo_atendimento_id, descricao, data_atendimento, horario_atendimento, status)
                    VALUES 
                    (:usuario_id, :pessoa_id, :tipo_atendimento_id, :descricao, :data_atendimento, :horario_atendimento, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':pessoa_id', $pessoa_id, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipo_atendimento_id, PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':data_atendimento', $data_atendimento);
            $stmt->bindValue(':horario_atendimento', $horario_atendimento);
            $stmt->bindValue(':status', 'aberto');
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento cadastrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar atendimento: ' . $e->getMessage()]);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? '');
        $observacao_final = trim($_POST['observacao_final'] ?? '');

        if (!$id || $status === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e status são obrigatórios.']);
            return;
        }

        // Validar se o status é um dos permitidos
        $statusPermitidos = ['aberto', 'em andamento', 'concluído'];
        if (!in_array($status, $statusPermitidos)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Permitidos: aberto, em andamento, concluído']);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos 
                    SET status = :status, observacao_final = :observacao_final
                    WHERE id = :id';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':observacao_final', $observacao_final ?: null);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['erro' => 'Atendimento não encontrado.']);
                return;
            }

            echo json_encode(['mensagem' => 'Atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar atendimento: ' . $e->getMessage()]);
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

        $sql = 'SELECT 
                    a.id,
                    a.data_atendimento,
                    a.horario_atendimento,
                    a.descricao,
                    a.status,
                    a.observacao_final,
                    a.usuario_id,
                    a.criado_em,
                    a.atualizado_em,
                    p.nome AS pessoa_nome,
                    p.documento AS pessoa_documento,
                    p.email AS pessoa_email,
                    p.telefone AS pessoa_telefone,
                    p.curso AS pessoa_curso,
                    ta.nome AS tipo_atendimento_nome,
                    ta.descricao AS tipo_atendimento_descricao,
                    u.nome AS usuario_nome,
                    u.email AS usuario_email
                FROM atendimentos a
                INNER JOIN pessoas p ON a.pessoa_id = p.id
                INNER JOIN tipos_atendimentos ta ON a.tipo_atendimento_id = ta.id
                INNER JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = :id';

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