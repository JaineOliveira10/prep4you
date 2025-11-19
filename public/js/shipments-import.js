let tsvData = null;
let currentTsvData = null;

// Função para atualizar preview com data de coleta
function updatePreview(shipmentDate) {
    if (!currentTsvData) {
        console.log('currentTsvData não definido');
        return;
    }
    
    console.log('Atualizando preview para data:', shipmentDate);
    calculateCollectionDate(shipmentDate)
        .then(collectionDate => {
            console.log('Atualizando HTML com data de coleta:', collectionDate);
            const updatedHtml = `
                <p><strong>ID do Envio:</strong> ${currentTsvData['ID do envio'] || 'N/A'}</p>
                <p><strong>Nome:</strong> ${currentTsvData.Nome || 'N/A'}</p>
                <p><strong>Enviar para:</strong> ${currentTsvData['Enviar para'] || 'N/A'}</p>
                <p><strong>Data de criação:</strong> ${new Date().toLocaleDateString('pt-BR')}</p>
                <p><strong>Data da Coleta:</strong> ${collectionDate.split('-').reverse().join('/')}</p>
            `;
            document.getElementById('previewData').innerHTML = updatedHtml;
        })
        .catch(error => {
            console.error('Erro ao calcular data de coleta:', error);
        });
}

// Função para calcular data de coleta
function calculateCollectionDate(shipmentDate) {
    console.log('Calculando data de coleta para:', shipmentDate);
    return fetch(window.shipmentRoutes.calculateCollectionDate, {
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
        console.log('Data de coleta calculada:', data.collection_date);
        return data.collection_date;
    });
}

// Listener para atualizar data de coleta quando mudar data da remessa
document.getElementById('shipmentDate').addEventListener('change', function() {
    const shipmentDate = this.value;
    if (shipmentDate && currentTsvData) {
        updatePreview(shipmentDate);
    }
});

// Quando arquivo é selecionado
document.getElementById('tsvFile').addEventListener('change', function() {
    if (this.files.length > 0) {
        document.getElementById('previewBtn').style.display = 'inline-block';
        document.getElementById('importBtn').style.display = 'none';
    }
});

// Botão visualizar
document.getElementById('previewBtn').addEventListener('click', function() {
    const fileInput = document.getElementById('tsvFile');
    if (!fileInput.files[0]) return;
    
    const formData = new FormData();
    formData.append('tsv_file', fileInput.files[0]);
    
    fetch(window.shipmentRoutes.preview, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            tsvData = data.data;
            currentTsvData = data.data;
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('previewSection').style.display = 'block';
            document.getElementById('previewBtn').style.display = 'none';
            document.getElementById('createBtn').style.display = 'inline-block';
            
            document.getElementById('shipmentDate').value = new Date().toISOString().split('T')[0];
            
            // Calcular data de coleta inicial
            updatePreview(document.getElementById('shipmentDate').value);
        } else {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar arquivo');
    });
});

// Botão criar remessa
document.getElementById('createBtn').addEventListener('click', function() {
    const shipmentDate = document.getElementById('shipmentDate').value;
    if (!shipmentDate) {
        alert('Por favor, selecione uma data para a remessa');
        return;
    }
    
    const formData = new FormData();
    formData.append('tsv_file', document.getElementById('tsvFile').files[0]);
    formData.append('shipment_date', shipmentDate);
    
    this.disabled = true;
    this.textContent = 'Criando...';
    
    fetch(window.shipmentRoutes.import, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('previewSection').style.display = 'none';
            document.getElementById('resultSection').style.display = 'block';
            document.getElementById('createBtn').style.display = 'none';
             window.location.reload();
        } else {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao criar remessa');
    })
    .finally(() => {
        this.disabled = false;
        this.textContent = 'Criar Remessa';
    });
});

// Reset modal quando fechar
document.getElementById('importModal').addEventListener('hidden.bs.modal', function() {
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