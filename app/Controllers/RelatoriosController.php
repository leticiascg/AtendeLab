<?php

require_once __DIR__ . '/../Middleware/auth.php';

class RelatoriosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function relatorioAtendimentos(): void
    {
        exigirAutenticacao();

        header('Content-Type: application/json; charset=utf-8');

        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;
        $status = $_GET['status'] ?? null;

        try {
            $sql = 'SELECT 
                        a.id,
                        a.data_atendimento,
                        a.horario_atendimento,
                        a.descricao,
                        a.status,
                        a.observacao_final,
                        p.nome AS pessoa_nome,
                        p.documento AS pessoa_documento,
                        p.email AS pessoa_email,
                        ta.nome AS tipo_atendimento_nome,
                        u.nome AS usuario_nome,
                        a.criado_em,
                        a.atualizado_em
                    FROM atendimentos a
                    INNER JOIN pessoas p ON a.pessoa_id = p.id
                    INNER JOIN tipos_atendimentos ta ON a.tipo_atendimento_id = ta.id
                    INNER JOIN usuarios u ON a.usuario_id = u.id
                    WHERE 1=1';

            if ($data_inicio) {
                $sql .= ' AND DATE(a.data_atendimento) >= :data_inicio';
            }

            if ($data_fim) {
                $sql .= ' AND DATE(a.data_atendimento) <= :data_fim';
            }

            if ($status) {
                $sql .= ' AND a.status = :status';
            }

            $sql .= ' ORDER BY a.data_atendimento DESC';

            $stmt = $this->pdo->prepare($sql);

            if ($data_inicio) {
                $stmt->bindValue(':data_inicio', $data_inicio);
            }

            if ($data_fim) {
                $stmt->bindValue(':data_fim', $data_fim);
            }

            if ($status) {
                $stmt->bindValue(':status', $status);
            }

            $stmt->execute();
            $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'filtros' => [
                    'data_inicio' => $data_inicio,
                    'data_fim' => $data_fim,
                    'status' => $status
                ],
                'total_registros' => count($atendimentos),
                'dados' => $atendimentos
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao gerar relatório de atendimentos.']);
        }
    }

    public function relatorioPessoas(): void
    {
        exigirAutenticacao();

        header('Content-Type: application/json; charset=utf-8');

        $status = $_GET['status'] ?? null;

        try {
            $sql = 'SELECT 
                        p.id,
                        p.nome,
                        p.documento,
                        p.email,
                        p.telefone,
                        p.curso,
                        p.periodo,
                        p.status,
                        COUNT(a.id) as total_atendimentos,
                        p.criado_em,
                        p.atualizado_em
                    FROM pessoas p
                    LEFT JOIN atendimentos a ON p.id = a.pessoa_id
                    WHERE 1=1';

            if ($status) {
                $sql .= ' AND p.status = :status';
            }

            $sql .= ' GROUP BY p.id ORDER BY p.nome ASC';

            $stmt = $this->pdo->prepare($sql);

            if ($status) {
                $stmt->bindValue(':status', $status);
            }

            $stmt->execute();
            $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'filtros' => [
                    'status' => $status
                ],
                'total_registros' => count($pessoas),
                'dados' => $pessoas
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao gerar relatório de pessoas.']);
        }
    }

    public function relatorioAtendimentosPorTipo(): void
    {
        exigirAutenticacao();

        header('Content-Type: application/json; charset=utf-8');

        try {
            $sql = 'SELECT 
                        ta.id,
                        ta.nome AS tipo_atendimento,
                        COUNT(a.id) as total_atendimentos,
                        SUM(CASE WHEN a.status = "aberto" THEN 1 ELSE 0 END) as abertos,
                        SUM(CASE WHEN a.status = "em andamento" THEN 1 ELSE 0 END) as em_andamento,
                        SUM(CASE WHEN a.status = "concluído" THEN 1 ELSE 0 END) as concluidos
                    FROM tipos_atendimentos ta
                    LEFT JOIN atendimentos a ON ta.id = a.tipo_atendimento_id
                    WHERE ta.status = "ativo"
                    GROUP BY ta.id, ta.nome
                    ORDER BY total_atendimentos DESC';

            $stmt = $this->pdo->query($sql);
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'total_tipos' => count($dados),
                'dados' => $dados
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao gerar relatório por tipo.']);
        }
    }

    public function relatorioAtendimentosPorUsuario(): void
    {
        exigirAutenticacao();

        header('Content-Type: application/json; charset=utf-8');

        try {
            $sql = 'SELECT 
                        u.id,
                        u.nome,
                        u.email,
                        u.perfil,
                        COUNT(a.id) as total_atendimentos,
                        SUM(CASE WHEN a.status = "aberto" THEN 1 ELSE 0 END) as abertos,
                        SUM(CASE WHEN a.status = "concluído" THEN 1 ELSE 0 END) as concluidos
                    FROM usuarios u
                    LEFT JOIN atendimentos a ON u.id = a.usuario_id
                    WHERE u.status = "ativo"
                    GROUP BY u.id, u.nome, u.email, u.perfil
                    ORDER BY total_atendimentos DESC';

            $stmt = $this->pdo->query($sql);
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'total_usuarios' => count($dados),
                'dados' => $dados
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao gerar relatório por usuário.']);
        }
    }
}

?>