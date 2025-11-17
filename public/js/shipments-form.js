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
            <td><input type="text" class="form-control kit-units" disabled></td>
            <td><input type="number" name="items[${this.itemIndex}][quantity]" class="form-control quantity" min="1" value="1" required></td>
            <td><input type="number" name="items[${this.itemIndex}][unit_price]" class="form-control unit-price" step="0.01" readonly></td>
            <td><input type="number" class="form-control total-value" step="0.01" disabled></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item">Remover</button></td>
        `;
        
        tbody.appendChild(row);
        this.itemIndex++;
        
        this.addRowEvents(row);
    }
    
    addRowEvents(row) {
        const productSearch = row.querySelector('.product-search');
        const productResults = row.querySelector('.product-results');
        const quantityInput = row.querySelector('.quantity');
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
        
        if (quantityInput) {
            quantityInput.addEventListener('input', () => {
                const productId = row.querySelector('.product-id')?.value;
                if (productId) {
                    this.updateProductInfo(row, productId);
                } else {
                    this.updateTotalValue(row);
                }
            });
        }
        
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                row.remove();
                this.updateGrandTotal();
            });
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
        
        console.log('Enviando requisição:', { product_id: productId, client_id: currentClientId, quantity: quantity });
        
        fetch(this.routes.getProductPrice, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                product_id: productId,
                client_id: currentClientId,
                quantity: quantity
            })
        })
        .then(response => {
            console.log('Status da resposta:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Resposta completa do servidor:', data);
            console.log('Preço retornado:', data.price);
            if (data.product) {
                console.log('Produto encontrado:', data.product);
                this.fillProductInfo(row, data.product, data.price);
            } else {
                console.error('Produto não encontrado na resposta');
            }
        })
        .catch(error => {
            console.error('Erro ao buscar informações do produto:', error);
        });
    }
    
    fillProductInfo(row, product, price) {
        try {
            const fsnkuInput = row.querySelector('.fsnku');
            const skuInput = row.querySelector('.sku');
            const typeInput = row.querySelector('.type_product');
            const kitUnitsInput = row.querySelector('.kit-units');
            const unitPriceInput = row.querySelector('.unit-price');
            
            if (fsnkuInput) fsnkuInput.value = product.fsnku || '';
            if (skuInput) skuInput.value = product.sku || '';
            if (unitPriceInput) unitPriceInput.value = price || 0;
            
            // Tratar campo kit_units
            if (kitUnitsInput) {
                if (product.type === 'kit' || product.type === 'super_kit') {
                    kitUnitsInput.value = product.kit_units || '';
                } else {
                    kitUnitsInput.value = '';
                }
            }
            
            // Tratar campo type com mais cuidado
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
        const quantityInput = row.querySelector('.quantity');
        const unitPriceInput = row.querySelector('.unit-price');
        const totalValueInput = row.querySelector('.total-value');
        
        if (quantityInput && unitPriceInput && totalValueInput) {
            const quantity = parseFloat(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            const totalValue = quantity * unitPrice;
            
            totalValueInput.value = totalValue.toFixed(2);
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
            totalItemsDisplay.textContent = totalItems;
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

// Cálculo da data de coleta
document.getElementById('shipment_date').addEventListener('change', function() {
    const shipmentDate = this.value;
    const collectionDateField = document.getElementById('collection_date');
    
    if (shipmentDate) {
        fetch(window.shipmentRoutes.calculateCollectionDate, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                shipment_date: shipmentDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.collection_date) {
                collectionDateField.value = data.collection_date;
            }
        })
        .catch(error => {
            console.error('Erro ao calcular data de coleta:', error);
        });
    }
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