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

// Função para obter preço do produto
async function getProductPrice(fsnku, sku, type) {
    try {
        const response = await fetch(window.shipmentRoutes.getProductPrice, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                fsnku: fsnku, 
                sku: sku,
                type: type 
            })
        });
        const data = await response.json();
        return data.price || 0;
    } catch (error) {
        return 0;
    }
}

// Função para atualizar preview com data de coleta
async function updatePreview(shipmentDate) {  // ✅ Adicionar async
    if (!currentTsvData) return;

    calculateCollectionDate(shipmentDate)
        .then(async collectionDate => {  // ✅ Adicionar async aqui também

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
                // Calcular totais (será atualizado após carregar preços)
                let totalItems = 0;                let totalValue = 0;
                
                currentTsvData.products.forEach(p => {
                    totalItems += parseInt(p.qtd) || 0;
                });

                productsHtml = `
                    <h6>Produtos encontrados:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">FNSKU</th>
                                    <th style="width: 25%;">Nome</th>
                                    <th style="width: 12%;">SKU</th>
                                    <th style="width: 6%;">Qtd</th>
                                    <th style="width: 7%;">Preço</th>
                                    <th style="width: 7%;">Total</th>
                                    <th style="width: 5%;">Cad?</th>
                                    <th style="width: 15%;">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${currentTsvData.products.map(p => {
                                    const itemTotal = (parseFloat(p.price || 0) * parseInt(p.qtd || 0)).toFixed(2);
                                    const truncatedName = truncateProductName(p.name || p.sku);
                                    const nameLines = truncatedName.match(/.{1,21}/g) || [truncatedName];
                                    const displayName = nameLines.slice(0, 2).join('<br>');
                                    
                                    return `
                                        <tr class="${!p.exists ? 'table-danger' : 'table-success'}" data-fsnku="${p.fsnku}">
                                            <td style="font-size: 0.85rem;">${p.fsnku}</td>
                                            <td style="font-size: 0.85rem; word-break: break-word;" title="${truncatedName}">${displayName}</td>
                                            <td style="font-size: 0.85rem;">${p.sku}</td>
                                            <td class="text-center text-end">${p.qtd}</td>
                                            <td class="product-price text-end">${(parseFloat(p.price || 0)).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                            <td class="product-total text-end">${parseFloat(itemTotal).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                            <td class="text-center">
                                                ${p.exists
                                                    ? '<span class="badge bg-success">Sim</span>'
                                                    : '<span class="badge bg-danger">Não</span>'}
                                            </td>
                                            <td>
                                                ${!p.exists ? `
                                                    <div class="d-flex gap-1 align-items-center flex-wrap" style="width: 125px;">
                                                        <select id="type_${p.fsnku}" 
                                                                class="form-select form-select-sm product-type w-100"
                                                                data-fsnku="${p.fsnku}" data-sku="${p.sku}">
                                                            <option value="simple">Simples</option>
                                                            <option value="kit">Kit</option>
                                                        </select>

                                                        <input type="number" id="kit_units_${p.fsnku}" 
                                                            class="form-control form-control-sm w-100"
                                                            placeholder="Qtd" min="1" style="display:none;">

                                                        <button class="btn btn-success btn-sm w-100"
                                                                onclick="registerProduct('${p.fsnku}', '${(p.name || p.sku).replace(/'/g, "\\'").replace(/"/g, '&quot;')}', '${p.sku}', '${p.asin || ''}')">
                                                            Cadastrar
                                                        </button>
                                                    </div>
                                                ` : ''}

                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            // Atualiza tudo no preview
            document.getElementById('previewData').innerHTML = headerHtml + productsHtml;
            
            // ✅ Mostrar o container de totais
            document.getElementById('totalsContainer').style.display = 'block';
            
            // ✅ Atualizar valores dos totais
            updateTotalValues();
            
            // Adicionar event listeners para os selects de tipo
            currentTsvData.products.forEach(p => {
                if (!p.exists) {
                    const typeSelect = document.getElementById(`type_${p.fsnku}`);
                    const kitInput = document.getElementById(`kit_units_${p.fsnku}`);
                    if (typeSelect && kitInput) {
                        typeSelect.addEventListener('change', async function() {
                            kitInput.style.display = this.value === 'kit' ? 'inline-block' : 'none';
                            await loadProductPrice(p.fsnku, p.sku, this.value);
                            updateTotalValues(); // ✅ Atualizar totais ao mudar tipo
                        });
                    }
                }
            });

            // Carregar preços dos produtos
            await loadAllProductPrices();
            
            // ✅ ATUALIZAR totais APÓS carregar todos os preços
            updateTotalValues();
            
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

// Função para atualizar preço na tabela
function updatePriceInTable(fsnku, price) {
    const row = document.querySelector(`tr[data-fsnku="${fsnku}"]`);
    if (row) {
        const product = currentTsvData.products.find(p => p.fsnku === fsnku);
        if (product) {
            product.price = parseFloat(price) || 0;
            
            const priceCell = row.querySelector('.product-price');
            const totalCell = row.querySelector('.product-total');
            const itemTotal = (parseFloat(product.price) * parseInt(product.qtd || 0)).toFixed(2);
            
            if (priceCell) {
                priceCell.textContent = `${parseFloat(product.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }
            if (totalCell) {
                totalCell.textContent = `${parseFloat(itemTotal).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }
            
            // ✅ Atualizar totais gerais após atualizar preço
            updateTotalValues();
        }
    }
}

// Função para atualizar totais gerais
function updateTotalValues() {

    let totalItems = 0;
    let totalValue = 0;
    
    if (!currentTsvData || !currentTsvData.products) return;
    currentTsvData.products.forEach(p => {
        const qtd = parseInt(p.qtd) || 0;
        const price = parseFloat(p.price) || 0;
        totalItems += qtd;
        totalValue += (price * qtd);
    });
    
    // ✅ Buscar elementos DO MODAL (não da tabela principal)
    const totalItemsEl = document.getElementById('modal-total-items');
    const totalValueEl = document.getElementById('modal-total-value');
    
    if (totalItemsEl) {
        totalItemsEl.textContent = totalItems.toLocaleString('pt-BR');
    }
    if (totalValueEl) {
        const formatted = 'R$ ' + totalValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        totalValueEl.textContent = formatted;
    }
}

// Função para carregar preço de um produto
async function loadProductPrice(fsnku, sku, type = 'simple') {
    try {
        const price = await getProductPrice(fsnku, sku, type);
        const product = currentTsvData.products.find(p => p.fsnku === fsnku);
        
        if (product) {
            product.price = parseFloat(price) || 0;
            updatePriceInTable(fsnku, product.price);
        }
    } catch (error) {
        console.error(`Erro ao carregar preço para ${fsnku}:`, error);
    }
}

// Função para carregar preços de todos os produtos COM DELAY
async function loadAllProductPrices() {    
    for (const product of currentTsvData.products) {
        await new Promise(resolve => setTimeout(resolve, 200)); // Delay de 200ms
        
        const type = !product.exists ? 
            (document.getElementById(`type_${product.fsnku}`)?.value || 'simple') : 
            'simple';
        
        await loadProductPrice(product.fsnku, product.sku, type);    } 
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
                exists: p.exists || false,
                price: 0  // Inicializar com 0
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
        return res.text().then(text => {
            try {
                return {
                    status: res.status,
                    data: JSON.parse(text)
                };
            } catch (e) {
                return {
                    status: res.status,
                    data: { error: 'Erro do servidor: ' + text.substring(0, 100) }
                };
            }
        });
    })
    .then(response => {        
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
            
            // Fechar modal e redirecionar para edição da remessa
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Redirecionar para a tela de edição com a remessa criada
                const shipmentId = response.data.shipment_id;
                if (shipmentId) {
                    window.location.href = `/shipments/${shipmentId}/edit?imported=true`;
                } else {
                    location.reload();
                }
            }, 1000);
        } else {
            const errorMessage = response.data.error || response.data.message || 'Erro ao criar remessa';
            document.getElementById('resultSection').innerHTML = `
                <div class="alert alert-danger">
                    <h6>${errorMessage}</h6>
                </div>
            `;
            document.getElementById('resultSection').style.display = 'block';
        }
    })
    .catch(error => {
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


