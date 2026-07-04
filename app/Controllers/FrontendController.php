<?php

require_once __DIR__ . '/../Middleware/auth.php';

class FrontendController
{
    public function __construct()
    {
        // Se precisar de acesso ao banco em alguma view, a conexão pode ser injetada ou requerida aqui.
    }

    public function usuarios(): void
    {
        exigirAutenticacao();
        require __DIR__ . '/../Views/usuarios/index.php';
    }

    public function usuariosForm(): void
    {
        exigirAutenticacao();
        require __DIR__ . '/../Views/usuarios/form.php';
    }

    public function atendimentos(): void
    {
        exigirAutenticacao();
        require __DIR__ . '/../Views/atendimentos/index.php';
    }

    public function pessoas(): void
    {
        exigirAutenticacao();
        require __DIR__ . '/../Views/pessoas/index.php';
    }

    public function relatorios(): void
    {
        exigirAutenticacao();
        require __DIR__ . '/../Views/relatorios/index.php';
    }

    public function tiposAtendimento(): void
    {
        exigirAutenticacao();
        require __DIR__ . '/../Views/tipos-atendimentos/index.php';
    }
}