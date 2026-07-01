<?php require_once __DIR__ . '/../layouts/config-view.php'; ?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">AtendeLab</span>

            <a class="btn btn-outline-light btn-sm" href="?controller=auth&action=logout">
                Sair
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4">Area restrita</h1>

                <p class="mb-1">
                    Bem-vindo,
                    <strong>
                        <?= htmlspecialchars(
                            $usuario['nome'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>.
                </p>

                <p class="text-muted">
                    Perfil:
                    <?= htmlspecialchars(
                        $usuario['perfil'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <a class="btn btn-primary" href="?controller=usuarios&action=listar">
                    Testar rota protegida de usuarios
                </a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <a href="<?= $baseUrl ?>?controller=frontend&action=pessoas" class="text-decoration-none text-dark">
                    <div class="card text-center shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Pessoas</h6>
                            <span id="totalPessoas" class="fs-3 fw-bold">...</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= $baseUrl ?>?controller=frontend&action=tipos" class="text-decoration-none text-dark">
                    <div class="card text-center shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Tipos</h6>
                            <span id="totalTipos" class="fs-3 fw-bold">...</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= $baseUrl ?>?controller=frontend&action=atendimentos" class="text-decoration-none text-dark">
                    <div class="card text-center shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted">Atendimentos</h6>
                            <span id="totalAtendimentos" class="fs-3 fw-bold">...</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl ?>assets/js/atend.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const targets = {
                pessoas: document.getElementById('totalPessoas'),
                tipos: document.getElementById('totalTipos'),
                atendimentos: document.getElementById('totalAtendimentos')
            };

            for (const [controller, element] of Object.entries(targets)) {
                try {
                    const response = await AtendeLabApi.get(controller, 'listar');
                    element.textContent = AtendeLabApi.toList(response).length;
                } catch (error) {
                    element.textContent = '0';
                    element.title = error.message;
                    console.error('Erro carregando dados do dashboard:', error);
                }
            }
        });
    </script>

</body>
</html>