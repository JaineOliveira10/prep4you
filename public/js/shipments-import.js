let tsvData = null;
let currentTsvData = null;

// Função para truncar nome do produto
function truncateProductName(name) {
    if (!name || name.length <= 40) return name;
    return name.substring(0, 18) + '....' + name.substring(name.length - 18);
}

// Função para cadastrar produto
function registerProduct(fsnku, name, sku, asin) {
    event.preventDefault();
    event.stopPropagation();
    
    const type = document.getElementById(`type_${fsnku}`).value;
    const kitUnits = type === 'kit' ? document.getElementById(`kit_units_${fsnku}`).value : 1;
    
    if (type === 'kit' && (!kitUnits || kitUnits < 1)) {
        alert('Informe a quantidade de itens no kit');
        return false;
    }
    
    console.log('Cadastrando produto via AJAX...');
    
    const formData = new FormData();
    formData.append('fsnku', fsnku);
    formData.append('name', truncateProductName(name));
    formData.append('sku', sku);
    formData.append('asin', asin || '');
    formData.append('type', type);
    formData.append('kit_units', kitUnits);
    formData.append('_token', window.csrfToken);
    
    fetch(window.shipmentRoutes.registerProduct, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            const product = currentTsvData.products.find(p => p.fsnku === fsnku);
            if (product) product.exists = true;
            updatePreview(document.getElementById('shipmentDate').value);
        } else {
            alert(res.error || res.message || 'Erro ao cadastrar produto');
        }
    })
    .catch(error => {
        alert('Erro de conexão ao cadastrar produto');
    });
    
    return false;
}

// Função para atualizar preview com data de coleta
function updatePreview(shipmentDate) {
    if (!currentTsvData) return;

    calculateCollectionDate(shipmentDate)
        .then(collectionDate => {

            let headerHtml = `
                <p><strong>ID do Envio:</strong> ${currentTsvData['ID do envio'] || 'N/A'}</p>
                <p><strong>Nome:</strong> ${currentTsvData.Nome || 'N/A'}</p>
                <p><strong>Enviar para:</strong> ${currentTsvData['Enviar para'] || 'N/A'}</p>
                <p><strong>Data de criação:</strong> ${new Date().toLocaleDateString('pt-BR')}</p>
                <p><strong>Data da Coleta:</strong> ${collectionDate.split('-').reverse().join('/')}</p>
            `;

            // Montar tabela de produtos
            let productsHtml = '';
            if (currentTsvData.products) {
                productsHtml = `
                    <h6>Produtos encontrados:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>FNSKU</th>
                                    <th>Nome</th>
                                    <th>SKU</th>
                                    <th>Qtd</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${currentTsvData.products.map(p => `
                                    <tr class="${!p.exists ? 'table-danger' : 'table-success'}">
                                        <td>${p.fsnku}</td>
                                        <td title="${p.name}">${truncateProductName(p.name)}</td>
                                        <td>${p.sku}</td>
                                        <td>${p.qtd}</td>
                                        <td>
                                            ${p.exists
                                                ? '<span class="badge bg-success">Cadastrado</span>'
                                                : '<span class="badge bg-danger">Não cadastrado</span>'}
                                        </td>
                                        <td>
                                            ${!p.exists ? `
                                                <div class="d-flex gap-1 align-items-center">
                                                    <select id="type_${p.fsnku}" class="form-select form-select-sm" style="width:80px;">
                                                        <option value="simple">Simples</option>
                                                        <option value="kit">Kit</option>
                                                    </select>
                                                    <input type="number" id="kit_units_${p.fsnku}" class="form-control form-control-sm" placeholder="Qtd Kit" min="1" style="width:80px; display:none;">
                                                    <button class="btn btn-sm btn-success" onclick="registerProduct('${p.fsnku}', '${p.name.replace(/'/g, "\\'").replace(/"/g, '&quot;')}', '${p.sku}', '${p.asin || ''}')">Cadastrar</button>
                                                </div>
                                                <script>
                                                    document.getElementById('type_${p.fsnku}').addEventListener('change', function() {
                                                        const kitInput = document.getElementById('kit_units_${p.fsnku}');
                                                        kitInput.style.display = this.value === 'kit' ? 'inline-block' : 'none';
                                                        if (this.value === 'kit') kitInput.required = true;
                                                    });
                                                </script>
                                            ` : ''}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            // Atualiza tudo no preview
            document.getElementById('previewData').innerHTML = headerHtml + productsHtml;
            
            // Adicionar event listeners para os selects de tipo
            currentTsvData.products.forEach(p => {
                if (!p.exists) {
                    const typeSelect = document.getElementById(`type_${p.fsnku}`);
                    const kitInput = document.getElementById(`kit_units_${p.fsnku}`);
                    if (typeSelect && kitInput) {
                        typeSelect.addEventListener('change', function() {
                            kitInput.style.display = this.value === 'kit' ? 'inline-block' : 'none';
                        });
                    }
                }
            });
            
            // Verificar se todos os produtos estão cadastrados
            const allRegistered = currentTsvData.products.every(p => p.exists);
            const createBtn = document.getElementById('createBtn');
            if (allRegistered) {
                createBtn.disabled = false;
                createBtn.textContent = 'Criar Remessa';
            } else {
                createBtn.disabled = true;
                createBtn.textContent = 'Cadastre todos os produtos primeiro';
            }
        });
}

// Função para calcular data de coleta
function calculateCollectionDate(shipmentDate) {
    return fetch(window.shipmentRoutes.calculateCollectionDate, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ shipment_date: shipmentDate })
    })
    .then(response => response.json())
    .then(data => data.collection_date);
}

// Listener para data da remessa
document.getElementById('shipmentDate').addEventListener('change', function () {
    if (currentTsvData) updatePreview(this.value);
});

// Habilita botão preview ao selecionar arquivo
document.getElementById('tsvFile').addEventListener('change', function () {
    if (this.files.length > 0) {
        document.getElementById('previewBtn').style.display = 'inline-block';
        document.getElementById('importBtn').style.display = 'none';
    }
});

// Botão VISUALIZAR
document.getElementById('previewBtn').addEventListener('click', function () {

    const fileInput = document.getElementById('tsvFile');
    if (!fileInput.files.length) return;

    const formData = new FormData();
    formData.append('tsv_file', fileInput.files[0]);
    formData.append('_token', window.csrfToken);

    fetch(window.shipmentRoutes.preview, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) {
            alert(res.error || 'Erro ao processar arquivo');
            return;
        }

        // Estrutura correta dos dados retornados
        const previewData = res.data;
        
        // Montar currentTsvData no formato esperado
        currentTsvData = {
            'ID do envio': previewData.data?.['ID do envio'] || 'N/A',
            'Nome': previewData.data?.['Nome'] || 'N/A', 
            'Enviar para': previewData.data?.['Enviar para'] || 'N/A',
            products: previewData.products?.map(p => ({
                fsnku: p.fsnku,
                name: p.name,
                sku: p.sku,
                asin: p.asin,
                qtd: p.qtd,
                exists: p.exists || false
            })) || []
        };

        document.getElementById('uploadSection').style.display = 'none';
        document.getElementById('previewSection').style.display = 'block';
        document.getElementById('previewBtn').style.display = 'none';
        document.getElementById('createBtn').style.display = 'inline-block';

        // Preenche data atual automaticamente
        document.getElementById('shipmentDate').value =
            new Date().toISOString().split('T')[0];

        updatePreview(document.getElementById('shipmentDate').value);
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar arquivo');
    });
});

// Criar remessa
document.getElementById('createBtn').addEventListener('click', function () {
    const shipmentDate = document.getElementById('shipmentDate').value;
    if (!shipmentDate) {
        alert('Escolha uma data para a remessa');
        return;
    }

    const formData = new FormData();
    formData.append('tsv_file', document.getElementById('tsvFile').files[0]);
    formData.append('shipment_date', shipmentDate);
    formData.append('products_data', JSON.stringify(currentTsvData.products));

    this.disabled = true;
    this.textContent = 'Criando...';

    fetch(window.shipmentRoutes.import, {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': window.csrfToken }
    })
    .then(res => {
        console.log('Status:', res.status);
        console.log('Response:', res);
        
        return res.text().then(text => {
            try {
                return {
                    status: res.status,
                    data: JSON.parse(text)
                };
            } catch (e) {
                console.error('Erro ao parsear JSON:', e);
                console.error('Resposta recebida:', text);
                return {
                    status: res.status,
                    data: { error: 'Erro do servidor: ' + text.substring(0, 100) }
                };
            }
        });
    })
    .then(response => {
        console.log('Parsed Response:', response);
        
        // Ocultar seções de upload e preview
        document.getElementById('uploadSection').style.display = 'none';
        document.getElementById('previewSection').style.display = 'none';

        if (response.status === 200 && response.data.success) {
            document.getElementById('resultSection').innerHTML = `
                <div class="alert alert-success">
                    <h6>Remessa criada com sucesso!</h6>
                </div>
            `;
            document.getElementById('resultSection').style.display = 'block';
            
            // Fechar modal e recarregar página após 2 segundos
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Recarregar a página
                setTimeout(() => {
                    location.reload();
                }, 500);
            }, 1000);
        } else {
            const errorMessage = response.data.error || response.data.message || 'Erro ao criar remessa';
            console.log('Error Message:', errorMessage);
            document.getElementById('resultSection').innerHTML = `
                <div class="alert alert-danger">
                    <h6>${errorMessage}</h6>
                </div>
            `;
            document.getElementById('resultSection').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Erro capturado:', error);
        document.getElementById('uploadSection').style.display = 'none';
        document.getElementById('previewSection').style.display = 'none';
        document.getElementById('resultSection').innerHTML = `
            <div class="alert alert-danger">
                <h6>Erro ao criar remessa: ${error.message}</h6>
            </div>
        `;
        document.getElementById('resultSection').style.display = 'block';
    })
    .finally(() => {
        this.disabled = false;
        this.textContent = 'Criar Remessa';
    });
});

// Reset modal
document.getElementById('importModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('uploadSection').style.display = 'block';
    document.getElementById('previewSection').style.display = 'none';
    document.getElementById('resultSection').style.display = 'none';
    document.getElementById('previewBtn').style.display = 'none';
    document.getElementById('importBtn').style.display = 'inline-block';
    document.getElementById('createBtn').style.display = 'none';
    document.getElementById('importForm').reset();
    tsvData = null;
    currentTsvData = null;
});
