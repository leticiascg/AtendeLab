<?php require_once __DIR__ . '/../layouts/config-view.php'; ?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Atendimentos - AtendeLab</title>

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
                    <h1 class="h4 mb-0">Atendimentos</h1>
                    <button class="btn btn-success" onclick="abrirFormularioNovo()">
                        Novo atendimento
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Pessoa</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaAtendimentos">
                            <tr>
                                <td colspan="6" class="text-center py-4">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4" id="cardFormulario" style="display: none;">
            <div class="card-body">
                <h2 class="h5 mb-3">Novo atendimento</h2>

                <form id="formAtendimento">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pessoa_id" class="form-label">Pessoa</label>
                            <select class="form-select" id="pessoa_id" name="pessoa_id" required>
                                <option value="">Selecione...</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tipo_atendimento_id" class="form-label">Tipo de atendimento</label>
                            <select class="form-select" id="tipo_atendimento_id" name="tipo_atendimento_id" required>
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data_atendimento" class="form-label">Data</label>
                            <input type="date" class="form-control" id="data_atendimento" name="data_atendimento" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="horario_atendimento" class="form-label">Horário</label>
                            <input type="time" class="form-control" id="horario_atendimento" name="horario_atendimento" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharFormulario()">Cancelar</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm" id="cardStatus" style="display: none;">
            <div class="card-body">
                <h2 class="h5 mb-3" id="tituloStatus">Alterar status</h2>

                <form id="formStatus">
                    <input type="hidden" id="atendimentoId" name="id" value="">
                    <input type="hidden" id="novoStatus" name="status" value="">

                    <div class="mb-3">
                        <label for="observacao_final" class="form-label">Observação final</label>
                        <textarea class="form-control" id="observacao_final" name="observacao_final" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Confirmar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharStatus()">Cancelar</button>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl ?>assets/js/atend.js"></script>

    <script>
        const formAtendimento = document.getElementById('formAtendimento');
        const formStatus = document.getElementById('formStatus');

        async function carregarOpcoes() {
            try {
                const response = await AtendeLabApi.get('atendimentos', 'opcoesFormulario');

                const selectPessoa = document.getElementById('pessoa_id');
                response.pessoas.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = `${p.nome} (${p.documento})`;
                    selectPessoa.appendChild(option);
                });

                const selectTipo = document.getElementById('tipo_atendimento_id');
                response.tipos.forEach(t => {
                    const option = document.createElement('option');
                    option.value = t.id;
                    option.textContent = t.nome;
                    selectTipo.appendChild(option);
                });
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        function badgeStatus(status) {
            const classes = {
                'aberto': 'text-bg-primary',
                'em andamento': 'text-bg-warning',
                'concluído': 'text-bg-success',
            };
            return classes[status] || 'text-bg-secondary';
        }

        async function carregarAtendimentos() {
            try {
                const dados = AtendeLabApi.toList(await AtendeLabApi.get('atendimentos', 'listar'));
                const tbody = document.getElementById('tabelaAtendimentos');
                if (!dados.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Nenhum atendimento cadastrado.</td></tr>';
                    return;
                }
                tbody.innerHTML = dados.map(a => `<tr>
                    <td>${AtendeLabApi.escape(a.pessoa_nome)}</td>
                    <td>${AtendeLabApi.escape(a.tipo_atendimento_nome)}</td>
                    <td>${AtendeLabApi.escape(a.data_atendimento)}</td>
                    <td>${AtendeLabApi.escape(a.descricao)}</td>
                    <td><span class="badge ${badgeStatus(a.status)}">${AtendeLabApi.escape(a.status)}</span></td>
                    <td class="text-end">
                        ${a.status === 'aberto' ? `<button class="btn btn-sm btn-outline-warning" onclick="abrirStatus(${Number(a.id)}, 'em andamento')">Em andamento</button>` : ''}
                        ${a.status !== 'concluído' ? `<button class="btn btn-sm btn-outline-success" onclick="abrirStatus(${Number(a.id)}, 'concluído')">Concluir</button>` : ''}
                    </td>
                </tr>`).join('');
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        }

        function abrirFormularioNovo() {
            formAtendimento.reset();
            document.getElementById('cardFormulario').style.display = 'block';
        }

        function fecharFormulario() {
            document.getElementById('cardFormulario').style.display = 'none';
            formAtendimento.reset();
        }

        function abrirStatus(id, status) {
            document.getElementById('atendimentoId').value = id;
            document.getElementById('novoStatus').value = status;
            document.getElementById('tituloStatus').textContent =
                status === 'concluído' ? 'Concluir atendimento' : 'Marcar como em andamento';
            document.getElementById('cardStatus').style.display = 'block';
        }

        function fecharStatus() {
            document.getElementById('cardStatus').style.display = 'none';
            formStatus.reset();
        }

        formAtendimento.addEventListener('submit', async event => {
            event.preventDefault();
            try {
                await AtendeLabApi.post('atendimentos', 'criar', new FormData(formAtendimento));
                AtendeLabApi.showAlert('alerta', 'Atendimento cadastrado com sucesso.');
                fecharFormulario();
                await carregarAtendimentos();
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        });

        formStatus.addEventListener('submit', async event => {
            event.preventDefault();
            try {
                await AtendeLabApi.post('atendimentos', 'atualizarStatus', new FormData(formStatus));
                AtendeLabApi.showAlert('alerta', 'Status atualizado com sucesso.');
                fecharStatus();
                await carregarAtendimentos();
            } catch (error) {
                AtendeLabApi.showAlert('alerta', error.message, 'danger');
            }
        });

        carregarOpcoes();
        carregarAtendimentos();
    </script>

</body>
</html>