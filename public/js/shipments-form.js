class ShipmentItemsManager {
    constructor(products, routes, csrfToken, userType, clientId) {
        this.products = products || [];
        this.routes = routes;
        this.csrfToken = csrfToken;
        this.userType = userType;
        this.clientId = clientId;
        this.itemIndex = document.querySelectorAll('#items-tbody tr').length;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.addEventsToExistingRows();
        this.updateGrandTotal();
    }
    
    bindEvents() {
        // Botão adicionar item
        document.getElementById('add-item').addEventListener('click', () => {
            this.addItemRow();
        });
        
        // Mudança de cliente (apenas admin)
        if (this.userType === 'admin') {
            const clientSelect = document.getElementById('client_id');
            if (clientSelect) {
                clientSelect.addEventListener('change', (e) => {
                    this.updateProductsByClient(e.target.value);
                });
            }
        }
    }
    
    addItemRow() {
        const tbody = document.getElementById('items-tbody');
        const row = document.createElement('tr');
        row.setAttribute('data-index', this.itemIndex);
        
        row.innerHTML = `
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control product-search" placeholder="Digite nome, SKU, FSNKU ou ASIN..." autocomplete="off">
                    <input type="hidden" name="items[${this.itemIndex}][product_id]" class="product-id">
                    <div class="product-results position-absolute w-100 bg-white border rounded shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                </div>
            </td>
            <td><input type="text" class="form-control fsnku" disabled></td>
            <td><input type="text" class="form-control sku" disabled></td>
            <td><input type="text" class="form-control type_product" disabled></td>
            <td><input type="text" class="form-control kit-units text-end" disabled></td>
            <td>
                <input type="text" class="form-control quantity-display text-end" placeholder="0" min="1">
                <input type="hidden" name="items[${this.itemIndex}][quantity]" class="quantity" value="0">
            </td>
            <td>
                <input type="text" class="form-control unit-price-display text-end" placeholder="0,00" disabled>
                <input type="hidden" name="items[${this.itemIndex}][unit_price]" class="unit-price" value="0.00">
            </td>
            <td>
                <input type="text" class="form-control total-value-display text-end" placeholder="R$ 0,00" disabled>
                <input type="hidden" name="total_value" class="total-value" value="0.00">
            </td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">Remover</button></td>
        `;
        
        tbody.appendChild(row);
        this.itemIndex++;
        
        this.addRowEvents(row);
    }
    
    addRowEvents(row) {
        const productSearch = row.querySelector('.product-search');
        const productResults = row.querySelector('.product-results');
        const quantityDisplay = row.querySelector('.quantity-display');
        const unitPriceDisplay = row.querySelector('.unit-price-display');
        const removeBtn = row.querySelector('.remove-item');
        
        if (productSearch) {
            let searchTimeout;
            
            productSearch.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length < 2) {
                    productResults.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    this.searchProducts(query, productResults, row);
                }, 300);
            });
            
            productSearch.addEventListener('blur', () => {
                setTimeout(() => {
                    productResults.style.display = 'none';
                }, 200);
            });
            
            // Reposicionar lista ao rolar a página
            window.addEventListener('scroll', () => {
                if (productResults.style.display === 'block') {
                    const rect = productSearch.getBoundingClientRect();
                    productResults.style.top = (rect.bottom + 2) + 'px';
                    productResults.style.left = rect.left + 'px';
                }
            });
            
            productSearch.addEventListener('focus', () => {
                if (productSearch.value.length >= 2) {
                    const rect = productSearch.getBoundingClientRect();
                    productResults.style.top = (rect.bottom + 2) + 'px';
                    productResults.style.left = rect.left + 'px';
                    productResults.style.width = rect.width + 'px';
                    productResults.style.display = 'block';
                }
            });
        }
        
        // Event listener para quantity-display
        if (quantityDisplay) {
            quantityDisplay.addEventListener('input', () => {
                const displayValue = quantityDisplay.value.replace(/\D/g, '');
                const quantityInput = row.querySelector('.quantity');
                if (quantityInput) {
                    quantityInput.value = displayValue;
                }
                this.updateTotalValue(row);
            });
        }
        
        // Event listener para unit-price-display
        if (unitPriceDisplay) {
            unitPriceDisplay.addEventListener('input', () => {
                const displayValue = unitPriceDisplay.value.replace(/\D/g, '').replace(',', '.');
                const unitPriceInput = row.querySelector('.unit-price');
                if (unitPriceInput) {
                    unitPriceInput.value = displayValue;
                }
                this.updateTotalValue(row);
            });
        }
        
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                row.remove();
                this.updateGrandTotal();
            });
        }
    }
    
    formatDisplayValues(row) {
        const quantityInput = row.querySelector('.quantity');
        const totalValueInput = row.querySelector('.total-value');
        
        if (quantityInput) {
            const value = parseInt(quantityInput.value) || 0;
            const formatted = value.toLocaleString('pt-BR');
            quantityInput.setAttribute('data-value', value); // Armazena valor real
            quantityInput.value = formatted; // Exibe formatado
            quantityInput.style.textAlign = 'right';
        }
        
        if (totalValueInput) {
            const value = parseFloat(totalValueInput.value) || 0;
            const formatted = value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            totalValueInput.setAttribute('data-value', value); // Armazena valor real
            totalValueInput.value = formatted; // Exibe formatado
            totalValueInput.style.textAlign = 'right';
        }
    }

    addEventsToExistingRows() {
        document.querySelectorAll('#items-tbody tr').forEach(row => {
            this.addRowEvents(row);
        });
    }
    
    updateProductInfo(row, productId) {
        if (!productId) {
            this.clearProductInfo(row);
            return;
        }
        
        const currentClientId = this.userType === 'admin' 
            ? document.getElementById('client_id')?.value 
            : this.clientId;
        
        const quantityInput = row.querySelector('.quantity');
        const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
        
        // Encontrar o produto para enviar também fsnku e sku
        const product = this.products.find(p => p.id == productId);
        
        const payload = {
            product_id: productId,
            fsnku: product?.fsnku,
            sku: product?.sku,
            type: product?.type,
            client_id: currentClientId,
            quantity: quantity
        };
        
        console.log('📤 ENVIANDO:', payload);
        
        fetch(this.routes.getProductPrice, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            console.log('📥 RECEBIDO:', data);
            console.log('Preço retornado:', data.price);
            
            if (product) {
                console.log('Produto encontrado:', product);
                this.fillProductInfo(row, product, data.price);
            } else {
                console.error('Produto não encontrado no array local');
            }
        })
        .catch(error => {
            console.error('Erro ao buscar preço:', error);
            this.clearProductInfo(row);
        });
    }
    
    fillProductInfo(row, product, price) {
        try {
            console.log('fillProductInfo chamado com:', { product, price }); // DEBUG
            
            const fsnkuInput = row.querySelector('.fsnku');
            const skuInput = row.querySelector('.sku');
            const typeInput = row.querySelector('.type_product');
            const kitUnitsInput = row.querySelector('.kit-units');
            const unitPriceInput = row.querySelector('.unit-price');
            const unitPriceDisplay = row.querySelector('.unit-price-display');
            
            if (fsnkuInput) fsnkuInput.value = product.fsnku || '';
            if (skuInput) skuInput.value = product.sku || '';
            // Garantir que sempre tenha 2 casas decimais
            if (unitPriceInput) {
                unitPriceInput.value = parseFloat(price || 0).toFixed(2);
                console.log('Preço formatado:', unitPriceInput.value); // DEBUG
            }
            
            // Atualizar campo de exibição do preço
            if (unitPriceDisplay) {
                const priceValue = parseFloat(price || 0);
                unitPriceDisplay.value = priceValue.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            
            // Tratar campo kit_units
            if (kitUnitsInput) {
                if (product.type === 'kit' || product.type === 'super_kit') {
                    kitUnitsInput.value = product.kit_units || '';
                } else {
                    kitUnitsInput.value = '';
                }
            }
            
            // Tratar campo type
            if (typeInput) {
                let typeDisplay = '';
                if (product.type === 'simple') {
                    typeDisplay = 'Simples';
                } else if (product.type === 'kit') {
                    typeDisplay = 'Kit';
                } else if (product.type === 'super_kit') {
                    typeDisplay = 'Super Kit';
                } else {
                    typeDisplay = product.type || '';
                }
                typeInput.value = typeDisplay;
            }
            
            this.updateTotalValue(row);
        } catch (error) {
            console.error('Erro ao preencher informações do produto:', error);
        }
    }
    
    clearProductInfo(row) {
        const inputs = row.querySelectorAll('.fsnku, .sku, .type_product, .kit-units, .unit-price, .total-value');
        inputs.forEach(input => {
            input.value = '';
        });
        this.updateGrandTotal();
    }
    
    updateTotalValue(row) {
        const quantityDisplay = row.querySelector('.quantity-display');
        const unitPriceInput = row.querySelector('.unit-price');
        const totalValueDisplay = row.querySelector('.total-value-display');
        const totalValueInput = row.querySelector('.total-value');
        
        if (quantityDisplay && unitPriceInput && totalValueDisplay) {
            // Extrair quantidade do campo de exibição
            const quantity = parseInt(quantityDisplay.value.replace(/\D/g, '')) || 0;
            // Usar o valor real do campo oculto (sem formatação)
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            const totalValue = (quantity * unitPrice);
            
            console.log('DEBUG updateTotalValue:', { quantity, unitPrice, totalValue }); // DEBUG
            
            // Atualizar campo oculto
            if (totalValueInput) {
                totalValueInput.value = totalValue.toFixed(2);
            }
            
            // Atualizar campo de exibição
            totalValueDisplay.value = totalValue.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        this.updateGrandTotal();
    }
    
    updateGrandTotal() {
        let grandTotal = 0;
        let totalItems = 0;
        
        document.querySelectorAll('.total-value').forEach(input => {
            grandTotal += parseFloat(input.value) || 0;
        });
        
        document.querySelectorAll('.quantity').forEach(input => {
            totalItems += parseInt(input.value) || 0;
        });
        
        const grandTotalDisplay = document.getElementById('grand-total');
        const grandTotalInput = document.getElementById('grand-total-input');
        const totalItemsDisplay = document.getElementById('grand-total-items-display');
        const totalItemsInput = document.getElementById('grand-total-items-input');
        
        if (grandTotalDisplay) {
            grandTotalDisplay.textContent = 'R$ ' + grandTotal.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        if (grandTotalInput) {
            grandTotalInput.value = grandTotal.toFixed(2);
        }
        
        if (totalItemsDisplay) {
            totalItemsDisplay.textContent = totalItems.toLocaleString('pt-BR');
        }
        
        if (totalItemsInput) {
            totalItemsInput.value = totalItems;
        }
    }
    
    searchProducts(query, resultsContainer, row) {
        const currentClientId = this.userType === 'admin' 
            ? document.getElementById('client_id')?.value 
            : this.clientId;
            
        const filteredProducts = this.products.filter(product => {
            return product.name.toLowerCase().includes(query.toLowerCase()) ||
                   (product.sku && product.sku.toLowerCase().includes(query.toLowerCase())) ||
                   (product.fsnku && product.fsnku.toLowerCase().includes(query.toLowerCase())) ||
                   (product.asin && product.asin.toLowerCase().includes(query.toLowerCase()));
        });
        
        if (filteredProducts.length === 0) {
            resultsContainer.innerHTML = '<div class="p-2 text-muted">Nenhum produto encontrado</div>';
        } else {
            resultsContainer.innerHTML = filteredProducts.map(product => `
                <div class="product-result-item p-2 border-bottom" style="cursor: pointer;" data-product-id="${product.id}">
                    <div class="fw-bold">${product.name}</div>
                    <small class="text-muted">
                        ${product.sku ? `SKU: ${product.sku}` : ''}
                        ${product.fsnku ? ` | FSNKU: ${product.fsnku}` : ''}
                        ${product.asin ? ` | ASIN: ${product.asin}` : ''}
                    </small>
                </div>
            `).join('');
            
            // Adicionar eventos de clique nos resultados
            resultsContainer.querySelectorAll('.product-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    const productId = item.dataset.productId;
                    const product = filteredProducts.find(p => p.id == productId);
                    
                    if (product) {
                        row.querySelector('.product-search').value = product.name;
                        row.querySelector('.product-id').value = productId;
                        resultsContainer.style.display = 'none';
                        this.updateProductInfo(row, productId);
                    }
                });
                
                item.addEventListener('mouseenter', () => {
                    item.style.backgroundColor = '#f8f9fa';
                });
                
                item.addEventListener('mouseleave', () => {
                    item.style.backgroundColor = 'white';
                });
            });
        }
        
        const searchInput = row.querySelector('.product-search');
        const rect = searchInput.getBoundingClientRect();
        resultsContainer.style.top = (rect.bottom + 2) + 'px';
        resultsContainer.style.left = rect.left + 'px';
        resultsContainer.style.width = rect.width + 'px';
        resultsContainer.style.display = 'block';
    }
    
    updateProductsByClient(clientId) {
        if (!clientId) return;
        
        fetch(this.routes.getProductsByClient, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                client_id: clientId
            })
        })
        .then(response => response.json())
        .then(data => {
            this.products = data;
            
            // Limpar itens existentes
            document.getElementById('items-tbody').innerHTML = '';
            this.itemIndex = 0;
            this.updateGrandTotal();
        })
        .catch(error => {
            console.error('Erro ao buscar produtos:', error);
        });
    }
}

// Função para formatar número com separador de milhar
function formatNumberInput(value) {
    // Remove tudo que não é número
    value = value.replace(/\D/g, '');
    // Formata com separador de milhar
    return new Intl.NumberFormat('pt-BR').format(value);
}

// Função para formatar decimal com separador de milhar
function formatDecimalInput(value) {
    // Remove tudo que não é número ou vírgula
    value = value.replace(/[^\d,]/g, '');
    // Se tiver mais de uma vírgula, remove as extras
    let parts = value.split(',');
    if (parts.length > 2) {
        value = parts[0] + ',' + parts.slice(1).join('');
    }
    return value;
}

// Event listener para quantity-display
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-display')) {
        const displayInput = e.target;
        const hiddenInput = displayInput.nextElementSibling;
        
        // Formata o valor exibido
        const formatted = formatNumberInput(displayInput.value);
        displayInput.value = formatted;
        
        // Armazena o valor sem formatação no campo oculto
        hiddenInput.value = displayInput.value.replace(/\D/g, '');
    }
});

// Event listener para unit-price-display
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('unit-price-display')) {
        const displayInput = e.target;
        const hiddenInput = displayInput.nextElementSibling;
        
        // Formata o valor exibido
        const formatted = formatDecimalInput(displayInput.value);
        displayInput.value = formatted;
        
        // Armazena o valor sem formatação no campo oculto
        let cleanValue = displayInput.value.replace('.', '').replace(',', '.');
        hiddenInput.value = cleanValue;
    }
});

// Antes de submeter o formulário, garante que os campos ocultos têm os valores corretos
document.querySelector('form').addEventListener('submit', function(e) {
    document.querySelectorAll('.quantity-display').forEach(function(input) {
        const hiddenInput = input.nextElementSibling;
        hiddenInput.value = input.value.replace(/\D/g, '');
    });
    
    document.querySelectorAll('.unit-price-display').forEach(function(input) {
        const hiddenInput = input.nextElementSibling;
        let cleanValue = input.value.replace('.', '').replace(',', '.');
        hiddenInput.value = cleanValue;
    });
});

// Cálculo da data de coleta
document.getElementById('shipment_date').addEventListener('change', function() {
    const shipmentDate = this.value;
    
    if (!shipmentDate) return;

    // Chamar a rota para calcular a data de coleta
    fetch(window.shipmentRoutes.calculateCollectionDate, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            shipment_date: shipmentDate
        })
    })
    .then(response => response.json())
    .then(data => {
        // Atualizar o input visível e o hidden
        const collectionDate = data.collection_date;
        document.getElementById('collection_date').value = collectionDate;
        document.querySelector('input[name="collection_date"]').value = collectionDate;
    })
    .catch(error => console.error('Erro ao calcular data de coleta:', error));
});

// Inicializar gerenciador de itens
document.addEventListener('DOMContentLoaded', function() {
    const itemsManager = new ShipmentItemsManager(
        window.products || [],
        {
            getProductPrice: window.shipmentRoutes.getProductPrice,
            getProductsByClient: window.shipmentRoutes.getProductsByClient
        },
        window.csrfToken,
        window.userType,
        window.clientId
    );
});