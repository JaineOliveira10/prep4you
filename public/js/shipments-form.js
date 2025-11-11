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
        
        let productOptions = '<option value="">Selecione um produto</option>';
        this.products.forEach(product => {
            productOptions += `<option value="${product.id}">${product.name}</option>`;
        });
        
        row.innerHTML = `
            <td>
                <select name="items[${this.itemIndex}][product_id]" class="form-select product-select" required>
                    ${productOptions}
                </select>
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
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity');
        const removeBtn = row.querySelector('.remove-item');
        
        if (productSelect) {
            productSelect.addEventListener('change', (e) => {
                this.updateProductInfo(row, e.target.value);
            });
        }
        
        if (quantityInput) {
            quantityInput.addEventListener('input', () => {
                // Recalcular preço baseado na nova quantidade
                const productSelect = row.querySelector('.product-select');
                if (productSelect && productSelect.value) {
                    this.updateProductInfo(row, productSelect.value);
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