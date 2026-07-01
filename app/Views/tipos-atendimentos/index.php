<?php require_once __DIR__ . '/../layouts/config-view.php'; ?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tipos de Atendimento - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">AtendeLab</span>

            <div>
                <a class="btn btn-outline-light btn-sm" href="<?= $baseUrl ?>?controller=auth&action=dashboard">
                    Dashboard
                </a>
                <a class="btn btn-outline-light btn-sm" href="<?= $baseUrl ?>?controller=auth&action=logout">
                    Sair
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <div id="alerta"></div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h4 mb-0">Tipos de atendimento</h1>
                    <button class="btn btn-success" onclick="abrirFormularioNovo()">
                        Novo tipo
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaTipos">
                            <tr>
                                <td colspan="4" class="text-center py-4">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" id="cardFormulario" style="display: none;">
            <div class="card-body">
                <h2 class="h5 mb-3" id="tituloFormulario">Novo tipo</h2>

                <form id="formTipo">
                    <input type="hidden" id="tipoId" name="id" value="">

                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharFormulario()">Cancelar</button>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl ?>assets/js/atend.js"></script>

    <script>
        const formTipo = document.getElementById('formTipo');

        async function carregarTipos() {
            try {
                const dados = AtendeLabApi.toList(await AtendeLabApi.get('tipos', 'listar'));
                const tbody = document.getElementById('tabelaTipos');
                if (!dados.length) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Nenhum tipo cadastrado.</td></tr>';
                    return;
                }
                tbody.innerHTML = dados.map(t => `<tr>
                    <td>${AtendeLabApi.escape(t.nome)}</td>
                    <td>${AtendeLabApi.escape(t.descricao || '')}</td>
                    <td><span class="badge ${t.status === 'ativo' ? 'text-bg-success' : 'text-bg-secondary'}">${AtendeLabApi.escape(t.status)}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarTipo(${Number(t.id)})">Editar</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="inativarTipo(${Number(t.id)})">Inativar</button>
                    </td>
                </tr>`).join('');
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        function abrirFormularioNovo() {
            formTipo.reset();
            document.getElementById('tipoId').value = '';
            document.getElementById('tituloFormulario').textContent = 'Novo tipo';
            document.getElementById('cardFormulario').style.display = 'block';
        }

        async function editarTipo(id) {
            try {
                const response = await AtendeLabApi.get('tipos', 'buscar', { id });
                const tipo = AtendeLabApi.toObject(response);

                document.getElementById('tipoId').value = tipo.id ?? '';
                document.getElementById('nome').value = tipo.nome ?? '';
                document.getElementById('descricao').value = tipo.descricao ?? '';

                document.getElementById('tituloFormulario').textContent = 'Editar tipo';
                document.getElementById('cardFormulario').style.display = 'block';
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        async function inativarTipo(id) {
            if (!confirm('Deseja realmente inativar este tipo de atendimento?')) return;

            try {
                await AtendeLabApi.post('tipos', 'inativar', { id });
                AtendeLabApi.showAlert('alerta', 'Tipo inativado com sucesso.');
                await carregarTipos();
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        function fecharFormulario() {
            document.getElementById('cardFormulario').style.display = 'none';
            formTipo.reset();
        }

        formTipo.addEventListener('submit', async event => {
            event.preventDefault();
            const id = document.getElementById('tipoId').value;
            try {
                await AtendeLabApi.post('tipos', id ? 'atualizar' : 'criar', new FormData(formTipo));
                AtendeLabApi.showAlert('alerta', id ? 'Tipo atualizado com sucesso.' : 'Tipo cadastrado com sucesso.');
                fecharFormulario();
                await carregarTipos();
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        });

        carregarTipos();
    </script>

</body>
</html>