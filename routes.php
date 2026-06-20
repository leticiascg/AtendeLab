<?php

require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/RelatoriosController.php';
require_once __DIR__ . '/app/Middleware/auth.php';

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

switch ($controller) {
    case 'auth':
        $authController = new AuthController();

        switch ($action) {
            case 'login':
                $authController->exibirLogin();
                break;

            case 'entrar':
                $authController->entrar();
                break;

            case 'dashboard':
                $authController->dashboard();
                break;

            case 'logout':
                $authController->logout();
                break;

            default:
                http_response_code(404);
                echo 'Acao de autenticacao nao encontrada.';
        }
        break;

    case 'usuarios':
        exigirAutenticacao();
        $usuariosController = new UsuariosController();

        switch ($action) {
            case 'listar':
                $usuariosController->listar();
                break;

            case 'buscarPorId':
            $usuariosController->buscarPorId();
            break;

            case 'criar':
                $usuariosController->criar();
                break;

            case 'atualizar':
                $usuariosController->atualizar();
                break;

            case 'excluir':
                $usuariosController->excluir();
                break;

            default:
                http_response_code(404);
                echo 'Acao de usuarios nao encontrada.';
        }
        break;

    case 'pessoas':
        exigirAutenticacao();
        $pessoasController = new PessoasController();
 
        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;
 
            case 'buscarPorId':
                $pessoasController->buscarPorId();
                break;
 
            case 'criar':
                $pessoasController->criar();
                break;
 
            case 'atualizar':
                $pessoasController->atualizar();
                break;
 
            case 'excluir':
                $pessoasController->excluir();
                break;

            case 'inativar':
                $pessoasController->inativar();
                break;
 
            default:
                http_response_code(404);
                echo 'Acao de pessoas nao encontrada.';
        }
        break;
 
    case 'tipos':
        exigirAutenticacao();
        $tiposAtendimentosController = new TiposAtendimentosController();
 
        switch ($action) {
            case 'listar':
                $tiposAtendimentosController->listar();
                break;
 
            case 'buscarPorId':
                $tiposAtendimentosController->buscarPorId();
                break;
 
            case 'criar':
                $tiposAtendimentosController->criar();
                break;
 
            case 'atualizar':
                $tiposAtendimentosController->atualizar();
                break;
 
            case 'excluir':
                $tiposAtendimentosController->excluir();
                break;

            case 'inativar':
                $tiposAtendimentosController->inativar();
                break;
 
            default:
                http_response_code(404);
                echo 'Acao de tipos de atendimentos nao encontrada.';
        }
        break;
 
    case 'atendimentos':
        exigirAutenticacao();
        $atendimentosController = new AtendimentosController();
 
        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;
 
            case 'buscarPorId':
                $atendimentosController->visualizar();
                break;
 
            case 'criar':
                $atendimentosController->criar();
                break;
 
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;
 
            default:
                http_response_code(404);
                echo 'Acao de atendimentos nao encontrada.';
        }
        break;
 
    case 'dashboard':
        exigirAutenticacao();
        $dashboardController = new DashboardController();
 
        switch ($action) {
            case 'index':
                $dashboardController->index();
                break;
 
            default:
                http_response_code(404);
                echo 'Acao de dashboard nao encontrada.';
        }
        break;
 
    case 'relatorios':
        exigirAutenticacao();
        $relatoriosController = new RelatoriosController();
 
        switch ($action) {
            case 'relatorioAtendimentos':
                $relatoriosController->relatorioAtendimentos();
                break;
 
            case 'relatorioPessoas':
                $relatoriosController->relatorioPessoas();
                break;
 
            case 'relatorioAtendimentosPorTipo':
                $relatoriosController->relatorioAtendimentosPorTipo();
                break;
 
            case 'relatorioAtendimentosPorUsuario':
                $relatoriosController->relatorioAtendimentosPorUsuario();
                break;
 
            default:
                http_response_code(404);
                echo 'Acao de relatorios nao encontrada.';
        }
        break;
 
    default:
        http_response_code(404);
        echo 'Controller nao encontrado.';
}
 
?>

