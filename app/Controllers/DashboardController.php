<?php

require_once __DIR__ . '/../Middleware/auth.php';

class DashboardController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        exigirAutenticacao();

        header('Content-Type: application/json; charset=utf-8');

        $usuario = usuarioAtual();

        try {
            $totalAtendimentos = $this->getTotalAtendimentos();
            $atendimentosAbertos = $this->getAtendimentosAbertos();
            $totalPessoas = $this->getTotalPessoas();
            $atendimentosRecentes = $this->getAtendimentosRecentes();

            echo json_encode([
                'usuario' => $usuario,
                'resumo' => [
                    'total_atendimentos' => $totalAtendimentos,
                    'atendimentos_abertos' => $atendimentosAbertos,
                    'total_pessoas' => $totalPessoas
                ],
                'atendimentos_recentes' => $atendimentosRecentes
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao carregar dashboard.']);
        }
    }

    private function getTotalAtendimentos(): int
    {
        $sql = 'SELECT COUNT(*) as total FROM atendimentos';
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    private function getAtendimentosAbertos(): int
    {
        $sql = 'SELECT COUNT(*) as total FROM atendimentos WHERE status = "aberto"';
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    private function getTotalPessoas(): int
    {
        $sql = 'SELECT COUNT(*) as total FROM pessoas WHERE status = "ativo"';
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    private function getAtendimentosRecentes(): array
    {
        $sql = 'SELECT 
                    a.id,
                    a.data_atendimento,
                    a.status,
                    p.nome AS pessoa_nome,
                    ta.nome AS tipo_atendimento_nome,
                    u.nome AS usuario_nome
                FROM atendimentos a
                INNER JOIN pessoas p ON a.pessoa_id = p.id
                INNER JOIN tipos_atendimentos ta ON a.tipo_atendimento_id = ta.id
                INNER JOIN usuarios u ON a.usuario_id = u.id
                ORDER BY a.data_atendimento DESC
                LIMIT 10';

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>