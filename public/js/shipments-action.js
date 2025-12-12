// Arquivo: /public/js/shipments-action.js
// Gerencia todas as ações relacionadas a remessas (editar, alterar status, deletar, baixar PDFs)

/**
 * Verifica permissões de status antes de permitir edição
 */
function checkStatusAndEdit(event, id, status) {
    event.preventDefault();
    const allowedStatuses = ['Pending', 'Apresenta Errors'];
    
    if (!allowedStatuses.includes(status)) {
        Swal.fire({
            icon: 'warning',
            title: 'Ação não permitida',
            text: `Você só pode editar remessas com status "Pendente" ou "Apresenta Erros".`,
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

    // Limpar apenas os campos visíveis, não os hidden inputs
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
            text: 'Selecione a ação desejada:',
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Possui Pendência',
            denyButtonText: 'Embalado',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showPendencyModal(shipmentId);
            } else if (result.isDenied) {
                updateStatusDirect(shipmentId, 'Packed');
            }
        });
        return;
    } else if (currentStatus === 'Has Pendency') {
        newStatus = 'In Preparation';
        statusLabel = 'Em Preparação';
        confirmationMessage.style.display = 'block';
        confirmationText.textContent = `Deseja retornar o status para "${statusLabel}"?`;
    } else if (currentStatus === 'Packed') {
        showCollectionProofModal(shipmentId);
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

    // Limpar apenas o campo de pendência
    document.getElementById('pendencyReason').value = '';
    
    // Setar os valores dos hidden inputs
    document.getElementById('shipmentId').value = shipmentId;
    document.getElementById('newStatus').value = 'Has Pendency';

    // Mostrar/ocultar campos
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

    // Limpar apenas o campo de arquivo
    document.getElementById('collectionProof').value = '';
    
    // Setar os valores dos hidden inputs
    document.getElementById('shipmentId').value = shipmentId;
    document.getElementById('newStatus').value = 'Collected';

    // Mostrar/ocultar campos
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
            // Fechar o modal Bootstrap imediatamente
            const modalElement = document.getElementById('statusChangeModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            
            // Mostrar sucesso e recarregar
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: data.message || 'Status atualizado com sucesso',
                confirmButtonText: 'Ok',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                // Recarregar após clicar Ok
                window.location.href = window.location.href;
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

// Flag global para evitar submissões duplicadas
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
    
    // Evitar submissões duplicadas
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
            // Fechar o modal Bootstrap imediatamente
            const modalElement = document.getElementById('statusChangeModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            
            // Mostrar sucesso e recarregar
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: data.message || 'Status atualizado com sucesso',
                confirmButtonText: 'Ok',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                isSubmitting = false;
                // Recarregar após clicar Ok
                window.location.href = window.location.href;
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