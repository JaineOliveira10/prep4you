/**
 * Verifica permissões de status antes de permitir edição
 */
function checkStatusAndEdit(event, id, status) {
    event.preventDefault();
    const allowedStatuses = ['Pending', 'Has Pendency'];
    
    if (!allowedStatuses.includes(status)) {
        Swal.fire({
            icon: 'warning',
            title: 'Ação não permitida',
            text: `Você só pode editar remessas com status "Pendente" ou "Possui Pendências".`,
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    window.location.href = `/shipments/${id}/edit`;
}

/**
 * Baixa os PDFs da remessa
 */
function downloadShipmentPdfs(event, id) {
    event.preventDefault();
    window.location.href = `/shipments/${id}/download-pdfs`;
}

/**
 * Baixa a ordem de preparação em PDF
 */
function downloadPreparationOrder(event, id) {
    event.preventDefault();
    window.location.href = `/shipments/${id}/download-preparation-order`;
}

/**
 * Verifica permissões de status antes de permitir deleção
 */
function checkStatusAndDelete(event, id, status) {
    event.preventDefault();
    const allowedStatuses = ['Pending', 'Apresenta Errors'];
    
    if (!allowedStatuses.includes(status)) {
        Swal.fire({
            icon: 'warning',
            title: 'Ação não permitida',
            text: `Você só pode excluir remessas com status "Pendente" ou "Apresenta Erros".`,
            confirmButtonText: 'Ok'
        });
        return;
    }
    
    confirmDelete('shipments-delete-' + id, 'Deseja realmente excluir esta remessa?');
}

/**
 * Exibe o modal de alteração de status
 * Ajusta os campos visíveis baseado no status atual
 */
function showStatusChangeModal(event, shipmentId, currentStatus) {
    event.preventDefault();

    const modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
    const pendencyField = document.getElementById('pendencyReasonField');
    const collectionProofField = document.getElementById('collectionProofField');
    const confirmationMessage = document.getElementById('confirmationMessage');
    const confirmationText = document.getElementById('confirmationText');

    document.getElementById('pendencyReason').value = '';
    document.getElementById('collectionProof').value = '';

    pendencyField.style.display = 'none';
    collectionProofField.style.display = 'none';
    confirmationMessage.style.display = 'none';

    document.getElementById('shipmentId').value = shipmentId;

    let newStatus = null;
    let statusLabel = '';

    if (currentStatus === 'Pending') {
        newStatus = 'In Preparation';
        statusLabel = 'Em Preparação';
        confirmationMessage.style.display = 'block';
        confirmationText.textContent = `Deseja alterar o status para "${statusLabel}"?`;
    } else if (currentStatus === 'In Preparation') {
        Swal.fire({
            title: 'Alterar Status',
            html: '<p style="margin-bottom: 20px; font-size: 14px; color: #666;">Selecione a ação desejada:</p>',
            icon: 'question',
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: true,
            allowEscapeKey: true,
            didOpen: (modal) => {
                const content = modal.querySelector('.swal2-content');
                
                // Criar container para os botões
                const buttonContainer = document.createElement('div');
                buttonContainer.style.display = 'grid';
                buttonContainer.style.gridTemplateColumns = '1fr';
                buttonContainer.style.gap = '12px';
                buttonContainer.style.marginTop = '20px';
                buttonContainer.style.width = '100%';
                buttonContainer.style.boxSizing = 'border-box';
                
                // Botão Possui Pendência (Danger - Vermelho)
                const pendencyBtn = document.createElement('button');
                pendencyBtn.textContent = 'Possui Pendência';
                pendencyBtn.style.width = '100%';
                pendencyBtn.style.padding = '12px';
                pendencyBtn.style.fontSize = '14px';
                pendencyBtn.style.fontWeight = '500';
                pendencyBtn.style.borderRadius = '5px';
                pendencyBtn.style.backgroundColor = '#dc3545';
                pendencyBtn.style.border = 'none';
                pendencyBtn.style.cursor = 'pointer';
                pendencyBtn.style.transition = 'background-color 0.3s';
                pendencyBtn.style.color = '#fff';
                pendencyBtn.onmouseover = () => pendencyBtn.style.backgroundColor = '#dc3545';
                pendencyBtn.onmouseout = () => pendencyBtn.style.backgroundColor = '#dc3545';
                pendencyBtn.onclick = () => {
                    Swal.close();
                    showPendencyModal(shipmentId);
                };
                
                // Botão Embalado (Success - Cinza)
                const packedBtn = document.createElement('button');
                packedBtn.textContent = 'Embalado';
                packedBtn.style.width = '100%';
                packedBtn.style.padding = '12px';
                packedBtn.style.fontSize = '14px';
                packedBtn.style.fontWeight = '500';
                packedBtn.style.borderRadius = '5px';
                packedBtn.style.backgroundColor = '#6c757d';
                packedBtn.style.border = 'none';
                packedBtn.style.cursor = 'pointer';
                packedBtn.style.transition = 'background-color 0.3s';
                packedBtn.style.color = '#fff';
                packedBtn.onmouseover = () => packedBtn.style.backgroundColor = '#6c757d';
                packedBtn.onmouseout = () => packedBtn.style.backgroundColor = '#6c757d';
                packedBtn.onclick = () => {
                    Swal.close();
                    updateStatusDirect(shipmentId, 'Packed');
                };
                
                // Botão Voltar para Pendente (Warning - Amarelo)
                const returnBtn = document.createElement('button');
                returnBtn.textContent = 'Voltar para Pendente';
                returnBtn.style.width = '100%';
                returnBtn.style.padding = '12px';
                returnBtn.style.fontSize = '14px';
                returnBtn.style.fontWeight = '500';
                returnBtn.style.borderRadius = '5px';
                returnBtn.style.backgroundColor = '#EA6A12';
                returnBtn.style.border = 'none';
                returnBtn.style.cursor = 'pointer';
                returnBtn.style.transition = 'background-color 0.3s';
                returnBtn.style.color = '#fff';
                returnBtn.onmouseover = () => returnBtn.style.backgroundColor = '#EA6A12';
                returnBtn.onmouseout = () => returnBtn.style.backgroundColor = '#EA6A12';
                returnBtn.onclick = () => {
                    Swal.close();
                    updateStatusDirect(shipmentId, 'Pending');
                };
                
                buttonContainer.appendChild(pendencyBtn);
                buttonContainer.appendChild(packedBtn);
                buttonContainer.appendChild(returnBtn);
                
                content.appendChild(buttonContainer);

                const header = modal.querySelector('.swal2-header');
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '×';
                closeBtn.style.position = 'absolute';
                closeBtn.style.top = '10px';
                closeBtn.style.right = '15px';
                closeBtn.style.backgroundColor = 'transparent';
                closeBtn.style.border = 'none';
                closeBtn.style.fontSize = '32px';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.color = '#999';
                closeBtn.style.padding = '0';
                closeBtn.style.width = '32px';
                closeBtn.style.height = '32px';
                closeBtn.style.lineHeight = '32px';
                closeBtn.style.transition = 'color 0.3s';
                closeBtn.onmouseover = () => closeBtn.style.color = '#333';
                closeBtn.onmouseout = () => closeBtn.style.color = '#999';
                closeBtn.onclick = () => Swal.close();
                header.style.position = 'relative';
                header.appendChild(closeBtn);
            }
        });
        return;
    } else if (currentStatus === 'Has Pendency') {
        newStatus = 'In Preparation';
        statusLabel = 'Em Preparação';
        confirmationMessage.style.display = 'block';
        confirmationText.textContent = `Deseja retornar o status para "${statusLabel}"?`;
    } else if (currentStatus === 'Packed') {
        Swal.fire({
            title: 'Alterar Status',
            html: '<p style="margin-bottom: 20px; font-size: 14px; color: #666;">Selecione a ação desejada:</p>',
            icon: 'question',
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: true,
            allowEscapeKey: true,
            didOpen: (modal) => {
                const content = modal.querySelector('.swal2-content');
                
                // Criar container para os botões
                const buttonContainer = document.createElement('div');
                buttonContainer.style.display = 'grid';
                buttonContainer.style.gridTemplateColumns = '1fr';
                buttonContainer.style.gap = '12px';
                buttonContainer.style.marginTop = '20px';
                buttonContainer.style.width = '100%';
                buttonContainer.style.boxSizing = 'border-box';
                
                // Botão Coletado (Success - Verde)
                const collectedBtn = document.createElement('button');
                collectedBtn.textContent = 'Coletado';
                collectedBtn.style.width = '100%';
                collectedBtn.style.padding = '12px';
                collectedBtn.style.fontSize = '14px';
                collectedBtn.style.fontWeight = '500';
                collectedBtn.style.borderRadius = '5px';
                collectedBtn.style.backgroundColor = '#28a745';
                collectedBtn.style.border = 'none';
                collectedBtn.style.cursor = 'pointer';
                collectedBtn.style.transition = 'background-color 0.3s';
                collectedBtn.style.color = '#fff';
                collectedBtn.onmouseover = () => collectedBtn.style.backgroundColor = '#218838';
                collectedBtn.onmouseout = () => collectedBtn.style.backgroundColor = '#28a745';
                collectedBtn.onclick = () => {
                    Swal.close();
                    showCollectionProofModal(shipmentId);
                };
                
                // Botão Voltar para Preparação (Azul)
                const returnBtn = document.createElement('button');
                returnBtn.textContent = 'Voltar para Preparação';
                returnBtn.style.width = '100%';
                returnBtn.style.padding = '12px';
                returnBtn.style.fontSize = '14px';
                returnBtn.style.fontWeight = '500';
                returnBtn.style.borderRadius = '5px';
                returnBtn.style.backgroundColor = '#6410F1';
                returnBtn.style.border = 'none';
                returnBtn.style.cursor = 'pointer';
                returnBtn.style.transition = 'background-color 0.3s';
                returnBtn.style.color = '#fff';
                returnBtn.onmouseover = () => returnBtn.style.backgroundColor = '#6410F1';
                returnBtn.onmouseout = () => returnBtn.style.backgroundColor = '#6410F1';
                returnBtn.onclick = () => {
                    Swal.close();
                    updateStatusDirect(shipmentId, 'In Preparation');
                };
                
                buttonContainer.appendChild(collectedBtn);
                buttonContainer.appendChild(returnBtn);
                
                content.appendChild(buttonContainer);
                
                // Adicionar botão de fechar (X) no header
                const header = modal.querySelector('.swal2-header');
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '×';
                closeBtn.style.position = 'absolute';
                closeBtn.style.top = '10px';
                closeBtn.style.right = '15px';
                closeBtn.style.backgroundColor = 'transparent';
                closeBtn.style.border = 'none';
                closeBtn.style.fontSize = '32px';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.color = '#999';
                closeBtn.style.padding = '0';
                closeBtn.style.width = '32px';
                closeBtn.style.height = '32px';
                closeBtn.style.lineHeight = '32px';
                closeBtn.style.transition = 'color 0.3s';
                closeBtn.onmouseover = () => closeBtn.style.color = '#333';
                closeBtn.onmouseout = () => closeBtn.style.color = '#999';
                closeBtn.onclick = () => Swal.close();
                header.style.position = 'relative';
                header.appendChild(closeBtn);
            }
        });
        return;
    } else if (currentStatus === 'Collected') {
        Swal.fire({
            title: 'Alterar Status',
            html: '<p style="margin-bottom: 20px; font-size: 14px; color: #666;">Selecione a ação desejada:</p>',
            icon: 'question',
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: true,
            allowEscapeKey: true,
            didOpen: (modal) => {
                const content = modal.querySelector('.swal2-content');
                
                // Criar container para o botão
                const buttonContainer = document.createElement('div');
                buttonContainer.style.display = 'grid';
                buttonContainer.style.gridTemplateColumns = '1fr';
                buttonContainer.style.gap = '12px';
                buttonContainer.style.marginTop = '20px';
                buttonContainer.style.width = '100%';
                buttonContainer.style.boxSizing = 'border-box';
                
                // Botão Voltar para Embalado (Warning - Amarelo)
                const returnBtn = document.createElement('button');
                returnBtn.textContent = 'Voltar para Embalado';
                returnBtn.style.width = '100%';
                returnBtn.style.padding = '12px';
                returnBtn.style.fontSize = '14px';
                returnBtn.style.fontWeight = '500';
                returnBtn.style.borderRadius = '5px';
                returnBtn.style.backgroundColor = '#6c757d';
                returnBtn.style.border = 'none';
                returnBtn.style.cursor = 'pointer';
                returnBtn.style.transition = 'background-color 0.3s';
                returnBtn.style.color = '#fff';
                returnBtn.onmouseover = () => returnBtn.style.backgroundColor = '#6c757d';
                returnBtn.onmouseout = () => returnBtn.style.backgroundColor = '#6c757d';
                returnBtn.onclick = () => {
                    Swal.close();
                    updateStatusDirect(shipmentId, 'Packed');
                };
                
                buttonContainer.appendChild(returnBtn);
                content.appendChild(buttonContainer);
                
                // Adicionar botão de fechar (X) no header
                const header = modal.querySelector('.swal2-header');
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '×';
                closeBtn.style.position = 'absolute';
                closeBtn.style.top = '10px';
                closeBtn.style.right = '15px';
                closeBtn.style.backgroundColor = 'transparent';
                closeBtn.style.border = 'none';
                closeBtn.style.fontSize = '32px';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.color = '#999';
                closeBtn.style.padding = '0';
                closeBtn.style.width = '32px';
                closeBtn.style.height = '32px';
                closeBtn.style.lineHeight = '32px';
                closeBtn.style.transition = 'color 0.3s';
                closeBtn.onmouseover = () => closeBtn.style.color = '#333';
                closeBtn.onmouseout = () => closeBtn.style.color = '#999';
                closeBtn.onclick = () => Swal.close();
                header.style.position = 'relative';
                header.appendChild(closeBtn);
            }
        });
        return;
    }

    if (newStatus) {
        document.getElementById('newStatus').value = newStatus;
        modal.show();
    }
}

/**
 * Exibe o modal para preenchimento do motivo da pendência
 */
function showPendencyModal(shipmentId) {
    const modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
    const pendencyField = document.getElementById('pendencyReasonField');
    const collectionProofField = document.getElementById('collectionProofField');
    const confirmationMessage = document.getElementById('confirmationMessage');

    document.getElementById('pendencyReason').value = '';
    
    document.getElementById('shipmentId').value = shipmentId;
    document.getElementById('newStatus').value = 'Has Pendency';

    pendencyField.style.display = 'block';
    collectionProofField.style.display = 'none';
    confirmationMessage.style.display = 'none';

    modal.show();
}

/**
 * Exibe o modal para upload do comprovante de coleta
 */
function showCollectionProofModal(shipmentId) {
    const modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
    const pendencyField = document.getElementById('pendencyReasonField');
    const collectionProofField = document.getElementById('collectionProofField');
    const confirmationMessage = document.getElementById('confirmationMessage');

    document.getElementById('collectionProof').value = '';
    
    document.getElementById('shipmentId').value = shipmentId;
    document.getElementById('newStatus').value = 'Collected';

    pendencyField.style.display = 'none';
    collectionProofField.style.display = 'block';
    confirmationMessage.style.display = 'none';

    modal.show();
}

/**
 * Atualiza o status da remessa diretamente (sem campos adicionais)
 * Usado para transições simples como Pending -> In Preparation ou In Preparation -> Packed
 */
function updateStatusDirect(shipmentId, newStatus) {
    const formData = new FormData();
    formData.append('status', newStatus);
    formData.append('_method', 'PATCH');
    formData.append('_token', getCsrfToken());

    fetch(`/shipments/${shipmentId}/update-status`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modalElement = document.getElementById('statusChangeModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: data.message || 'Status atualizado com sucesso',
                confirmButtonText: 'Ok',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro na API',
                text: data.error || 'Erro ao atualizar status',
                confirmButtonText: 'Ok'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao atualizar status',
            confirmButtonText: 'Ok'
        });
    });
}

/**
 * Obtém o token CSRF da página
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
           document.querySelector('input[name="_token"]')?.value || '';
}

let isSubmitting = false;

/**
 * Inicializa os event listeners quando o DOM está pronto
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('statusChangeForm');
    if (form) {
        form.addEventListener('submit', handleStatusFormSubmit);
    }
});

/**
 * Manipulador do envio do formulário de alteração de status
 */
function handleStatusFormSubmit(e) {
    e.preventDefault();
    
    if (isSubmitting) {
        return;
    }
    
    isSubmitting = true;

    const newStatus = document.getElementById('newStatus').value;
    const pendencyReasonField = document.getElementById('pendencyReason');
    const collectionProofField = document.getElementById('collectionProof');

    if (newStatus === 'Has Pendency' && !pendencyReasonField.value.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo obrigatório',
            text: 'Por favor, informe o motivo da pendência',
            confirmButtonText: 'Ok'
        });
        isSubmitting = false;
        return;
    }

    if (newStatus === 'Collected' && !collectionProofField.files.length) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo obrigatório',
            text: 'Por favor, envie o comprovante de coleta',
            confirmButtonText: 'Ok'
        });
        isSubmitting = false;
        return;
    }

    const shipmentId = document.getElementById('shipmentId').value;
    const formData = new FormData(this);

    fetch(`/shipments/${shipmentId}/update-status`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) {
                return Promise.reject(data);
            }
            return data;
        });
    })
    .then(data => {
        if (data.success) {
            const modalElement = document.getElementById('statusChangeModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: data.message || 'Status atualizado com sucesso',
                confirmButtonText: 'Ok',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                isSubmitting = false;
                window.location.reload();
            });
        } else {
            isSubmitting = false;
            Swal.fire({
                icon: 'error',
                title: 'Erro na API',
                text: data.error || 'Erro ao atualizar status',
                confirmButtonText: 'Ok'
            });
        }
    })
    .catch(error => {
        isSubmitting = false;
        
        let errorMessage = 'Erro ao atualizar status';
        if (error?.error) {
            errorMessage = error.error;
        } else if (error?.message) {
            errorMessage = error.message;
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Erro ao atualizar',
            text: errorMessage,
            confirmButtonText: 'Ok'
        });
    });
}