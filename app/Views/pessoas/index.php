<?php require_once __DIR__ . '/../layouts/config-view.php'; ?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pessoas - AtendeLab</title>

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
                    <h1 class="h4 mb-0">Pessoas</h1>
                    <button class="btn btn-success" onclick="abrirFormularioNovo()">
                        Nova pessoa
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Documento</th>
                                <th>E-mail</th>
                                <th>Curso</th>
                                <th>Período</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaPessoas">
                            <tr>
                                <td colspan="7" class="text-center py-4">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" id="cardFormulario" style="display: none;">
            <div class="card-body">
                <h2 class="h5 mb-3" id="tituloFormulario">Nova pessoa</h2>

                <form id="formPessoa">
                    <input type="hidden" id="pessoaId" name="id" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="documento" class="form-label">Documento</label>
                            <input type="text" class="form-control" id="documento" name="documento" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="curso" class="form-label">Curso</label>
                            <input type="text" class="form-control" id="curso" name="curso">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="periodo" class="form-label">Período</label>
                            <input type="text" class="form-control" id="periodo" name="periodo">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
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
        const formPessoa = document.getElementById('formPessoa');

        async function carregarPessoas() {
            try {
                const dados = AtendeLabApi.toList(await AtendeLabApi.get('pessoas', 'listar'));
                const tbody = document.getElementById('tabelaPessoas');
                if (!dados.length) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Nenhuma pessoa cadastrada.</td></tr>';
                    return;
                }
                tbody.innerHTML = dados.map(p => `<tr>
                    <td>${AtendeLabApi.escape(p.nome)}</td>
                    <td>${AtendeLabApi.escape(p.documento)}</td>
                    <td>${AtendeLabApi.escape(p.email)}</td>
                    <td>${AtendeLabApi.escape(p.curso || '')}</td>
                    <td>${AtendeLabApi.escape(p.periodo || '')}</td>
                    <td><span class="badge ${p.status === 'ativo' ? 'text-bg-success' : 'text-bg-secondary'}">${AtendeLabApi.escape(p.status)}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarPessoa(${Number(p.id)})">Editar</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="inativarPessoa(${Number(p.id)})">Inativar</button>
                    </td>
                </tr>`).join('');
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        function abrirFormularioNovo() {
            formPessoa.reset();
            document.getElementById('pessoaId').value = '';
            document.getElementById('tituloFormulario').textContent = 'Nova pessoa';
            document.getElementById('cardFormulario').style.display = 'block';
        }

        function validarId(id) {
            const parsedId = Number(id);
            if (!Number.isInteger(parsedId) || parsedId <= 0) {
                throw new Error('ID inválido no frontend.');
            }
            return parsedId;
        }

        async function editarPessoa(id) {
            try {
                const parsedId = validarId(id);
                const response = await AtendeLabApi.get('pessoas', 'buscarPorId', { query: { id: parsedId } });
                const pessoa = AtendeLabApi.toObject(response);

                document.getElementById('pessoaId').value = pessoa.id ?? '';
                document.getElementById('nome').value = pessoa.nome ?? '';
                document.getElementById('documento').value = pessoa.documento ?? '';
                document.getElementById('email').value = pessoa.email ?? '';
                document.getElementById('telefone').value = pessoa.telefone ?? '';
                document.getElementById('curso').value = pessoa.curso ?? '';
                document.getElementById('periodo').value = pessoa.periodo ?? '';
                document.getElementById('observacoes').value = pessoa.observacoes ?? '';
                document.getElementById('status').value = pessoa.status ?? 'ativo';

                document.getElementById('tituloFormulario').textContent = 'Editar pessoa';
                document.getElementById('cardFormulario').style.display = 'block';
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        async function inativarPessoa(id) {
            if (!confirm('Deseja realmente inativar esta pessoa?')) return;

            try {
                const parsedId = validarId(id);
                await AtendeLabApi.post('pessoas', 'inativar', { id: parsedId });
                AtendeLabApi.showAlert('alerta', 'Pessoa inativada com sucesso.');
                await carregarPessoas();
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        function fecharFormulario() {
            document.getElementById('cardFormulario').style.display = 'none';
            formPessoa.reset();
        }

        formPessoa.addEventListener('submit', async event => {
            event.preventDefault();
            const id = document.getElementById('pessoaId').value;
            try {
                await AtendeLabApi.post('pessoas', id ? 'atualizar' : 'criar', new FormData(formPessoa));
                AtendeLabApi.showAlert('alerta', id ? 'Pessoa atualizada com sucesso.' : 'Pessoa cadastrada com sucesso.');
                fecharFormulario();
                await carregarPessoas();
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        });

        carregarPessoas();
    </script>

</body>
</html>