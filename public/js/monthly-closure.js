let newClosureData = {
   year_month: null,
   client_id: null,
   closure_id: null
};

let viewingClosureData = {
   closure_id: null,
   year: null,
   month: null,
   client_id: null
};

function previewNewClosure() {
   const yearMonthInput = document.getElementById('year_month');
   const clientIdSelect = document.getElementById('closure_client_id');
   
   let yearMonth = yearMonthInput.value || yearMonthInput.getAttribute('data-value');
   yearMonth = yearMonth ? yearMonth.trim() : '';
   const clientId = clientIdSelect.value.trim();

   if (!yearMonth || !clientId || clientId === '') {
      Swal.fire({
         icon: 'warning',
         title: 'Validação',
         text: 'Por favor selecione ano/mês e cliente'
      });
      return;
   }

   // Armazenar dados
   newClosureData = {
      year_month: yearMonth,
      client_id: parseInt(clientId),
      closure_id: null
   };

   // Mostrar loading
   document.getElementById('loadingMessage').style.display = 'block';
   document.getElementById('step2Preview').style.display = 'none';

   // Verificar se já existe fechamento para este período e cliente
   checkExistingClosure(yearMonth, parseInt(clientId))
      .then(exists => {
         if (exists) {
            document.getElementById('loadingMessage').style.display = 'none';
            Swal.fire({
               icon: 'warning',
               title: 'Fechamento Existente',
               text: 'Já existe um fechamento para este cliente neste período. É necessário excluir o fechamento anterior antes de criar um novo.',
               showCancelButton: true,
               confirmButtonText: 'Ir para Listagem',
               cancelButtonText: 'Cancelar'
            }).then((result) => {
               if (result.isConfirmed) {
                  // Fechar modal e redirecionar para listagem
                  const modal = bootstrap.Modal.getInstance(document.getElementById('newClosureModal'));
                  modal.hide();
               }
            });
            return;
         }
         
         // Continuar com o preview
         loadClosurePreview(yearMonth);
      });
}

function checkExistingClosure(yearMonth, clientId) {
   const [year, month] = yearMonth.split('-');
   
   return fetch('/api/monthly-closure/check-existing', {
      method: 'POST',
      headers: {
         'Content-Type': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
         year: parseInt(year),
         month: parseInt(month),
         client_id: clientId
      })
   })
   .then(response => response.json())
   .then(data => data.exists || false)
   .catch(error => {
      console.error('Erro ao verificar fechamento:', error);
      return false;
   });
}

function loadClosurePreview(yearMonth) {
   const clientIdSelect = document.getElementById('closure_client_id');
   const clientName = clientIdSelect.options[clientIdSelect.selectedIndex].text;
   const clientId = newClosureData.client_id;

   // Buscar dados da procedure sem criar ainda
   const [year, month] = yearMonth.split('-');

   fetch(`/api/monthly-closure/preview`, {
      method: 'POST',
      headers: {
         'Content-Type': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
         year: parseInt(year),
         month: parseInt(month),
         client_id: parseInt(clientId)
      })
   })
   .then(response => response.json())
   .then(data => {      
      if (data.error) {
         throw new Error(data.error);
      }

      if (!data.shipments || data.shipments.length === 0) {
         document.getElementById('loadingMessage').style.display = 'none';
         Swal.fire({
            icon: 'warning',
            title: 'Sem Remessas',
            text: 'O cliente não possui remessas neste período. Não é possível criar fechamento sem remessas.'
         });
         return;
      }

      document.getElementById('new-closure-month').textContent = `${month}/${year}`;
      document.getElementById('new-closure-client').textContent = clientName;
      document.getElementById('new-closure-simple-labels').textContent = data.total_simple_labels || 0;
      document.getElementById('new-closure-kit-labels').textContent = data.total_kit_labels || 0;
      document.getElementById('new-closure-superkit-labels').textContent = data.total_superkit_labels || 0;
      document.getElementById('new-closure-unit-simple').textContent = 'R$ ' + parseFloat(data.unit_price_simple || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-unit-kit').textContent = 'R$ ' + parseFloat(data.unit_price_kit || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-simple-net').textContent = 'R$ ' + parseFloat(data.total_simple_net || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-kit-net').textContent = 'R$ ' + parseFloat(data.total_kit_net || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-superkit-value').textContent = 'R$ ' + parseFloat(data.total_superkit_value || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-price-range').textContent = data.price_range || '-';
      document.getElementById('new-closure-simple-discount').textContent = 'R$ ' + parseFloat(data.total_discount_simple || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-kit-discount').textContent = 'R$ ' + parseFloat(data.total_discount_kit || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-gross').textContent = 'R$ ' + parseFloat(data.total_gross || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-discount').textContent = 'R$ ' + parseFloat(data.total_discount || 0).toFixed(2).replace('.', ',');
      document.getElementById('new-closure-net').textContent = 'R$ ' + parseFloat(data.total_net || 0).toFixed(2).replace('.', ',');

      // Preencher remessas
      let shipmentsHtml = '';
      if (data.shipments && data.shipments.length > 0) {
         shipmentsHtml = '<table class="table table-sm table-striped"><thead><tr><th>ID Remessa</th><th>Data</th><th>Qtd</th><th>Valor</th></tr></thead><tbody>';
         data.shipments.forEach(shipment => {
            shipmentsHtml += `<tr>
               <td>${shipment.shipment_code}</td>
               <td>${shipment.creation_date}</td>
               <td>${shipment.total_items || 0}</td>
               <td>R$ ${parseFloat(shipment.value || 0).toFixed(2).replace('.', ',')}</td>
            </tr>`;
         });
         shipmentsHtml += '</tbody></table>';
      } else {
         shipmentsHtml = '<p class="text-muted">Nenhuma remessa encontrada</p>';
      }
      document.getElementById('new-closure-shipments').innerHTML = shipmentsHtml;

      // Trocar para step 2
      document.getElementById('loadingMessage').style.display = 'none';
      document.getElementById('step1Selection').style.display = 'none';
      document.getElementById('step2Preview').style.display = 'block';
      document.getElementById('step1Footer').style.display = 'none';
      document.getElementById('step2Footer').style.display = 'block';
   })
   .catch(error => {
      console.error('Erro:', error);
      document.getElementById('loadingMessage').style.display = 'none';
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Erro ao carregar preview: ' + error.message
      });
   });
}

// Voltar para step 1
function backToStep1() {
   document.getElementById('step1Selection').style.display = 'block';
   document.getElementById('step2Preview').style.display = 'none';
   document.getElementById('step1Footer').style.display = 'block';
   document.getElementById('step2Footer').style.display = 'none';
}

// Salvar o novo fechamento (executar procedure)
function saveNewClosure() {
   // Usar dados armazenados em newClosureData
   const yearMonth = newClosureData.year_month;
   const clientId = newClosureData.client_id;

   if (!yearMonth || !clientId) {
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Dados insuficientes para criar o fechamento'
      });
      return;
   }

   const formData = new FormData();
   formData.append('year_month', yearMonth);
   formData.append('client_id', clientId);
   formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

   // Salvar no banco (executar procedure)
   fetch(window.storeRoute || '/monthly-closures', {
      method: 'POST',
      body: formData
   })
   .then(response => {
      if (!response.ok) {
         return response.json().then(data => {
            throw new Error(data.message || 'Erro ao criar fechamento');
         });
      }
      return response.json();
   })
   .then(data => {
      // Sucesso ao salvar, agora fazer download do PDF
      downloadNewClosurePdf();
   })
   .catch(error => {
      console.error(error);
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: error.message || 'Erro ao criar fechamento'
      });
   });
}

// Fazer download do PDF após salvar
function downloadNewClosurePdf() {
   const [year, month] = newClosureData.year_month.split('-');
   const formData = new FormData();
   formData.append('year', parseInt(year));
   formData.append('month', parseInt(month));
   formData.append('client_id', newClosureData.client_id);
   formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

   fetch(window.previewPdfRoute || '/api/monthly-closure/preview-pdf', {
      method: 'POST',
      body: formData
   })
   .then(response => {
      if (!response.ok) {
         return response.text().then(text => {
            console.error(text);
            throw new Error('Erro ao gerar PDF');
         });
      }

      const disposition = response.headers.get('Content-Disposition');
      let filename = 'fechamento.pdf';

      if (disposition && disposition.includes('filename=')) {
         filename = disposition.split('filename=')[1].replace(/"/g, '').trim();
      }

      return response.blob().then(blob => ({ blob, filename }));
   })
   .then(({ blob, filename }) => {
      if (blob.size === 0) {
         throw new Error('PDF gerado vazio');
      }

      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');

      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();

      Swal.fire({
         icon: 'success',
         title: 'Sucesso',
         text: 'Fechamento criado com sucesso! Realizando download do PDF...',
         didClose: () => { 
            location.reload(); 
         }
      });

      setTimeout(() => URL.revokeObjectURL(url), 5000);
   })
   .catch(error => {
      console.error(error);
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: error.message
      });
   });
}

// Confirmar e criar o fechamento (versão antiga para download direto)
function confirmNewClosure(payload, successMessage = 'Download iniciado com sucesso!') {
   const formData = new FormData();

   // ✅ se veio closure_id (listagem)
   if (payload.closure_id) {
      formData.append('closure_id', payload.closure_id);
      formData.append('client_id', payload.client_id);
   }
   // ✅ se veio year/month/client_id (criação)
   else {
      if (!payload.year_month || !payload.client_id) {
         Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Dados insuficientes para gerar o PDF'
         });
         return;
      }

      formData.append('year_month', payload.year_month);
      formData.append('client_id', payload.client_id);
   }

   formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

   fetch(window.previewPdfRoute || '/api/monthly-closure/preview-pdf', {
      method: 'POST',
      body: formData
   })
   .then(response => {
      if (!response.ok) {
         return response.text().then(text => {
            console.error(text);
            throw new Error('Erro ao gerar PDF');
         });
      }

      const disposition = response.headers.get('Content-Disposition');
      let filename = 'fechamento.pdf';

      if (disposition && disposition.includes('filename=')) {
         filename = disposition.split('filename=')[1].replace(/"/g, '').trim();
      }

      return response.blob().then(blob => ({ blob, filename }));
   })
   .then(({ blob, filename }) => {
      if (blob.size === 0) {
         throw new Error('PDF gerado vazio');
      }

      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');

      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();

      Swal.fire({
         icon: 'success',
         title: 'Sucesso',
         text: successMessage,
         didClose: () => { 
            location.reload(); 
         }
      });

      setTimeout(() => URL.revokeObjectURL(url), 5000);
   })
   .catch(error => {
      console.error(error);
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: error.message
      });
   });
}



function showClosureModal(year, month, clientId) {
   viewingClosureData.year = year;
   viewingClosureData.month = month;
   viewingClosureData.client_id = clientId;

   openClosurePdf();
}

function deleteClosureConfirm(closureId, clientId) {
   Swal.fire({
      icon: 'warning',
      title: 'Excluir Fechamento',
      text: 'Tem certeza que deseja excluir este fechamento?',
      showCancelButton: true,
      confirmButtonText: 'Sim, excluir',
      cancelButtonText: 'Cancelar'
   }).then((result) => {
      if (result.isConfirmed) {
         const formId = 'delete-closure-' + closureId + '-' + clientId;
         document.getElementById(formId).submit();
      }
   });
}

function openClosurePdf() {
   if (!viewingClosureData.year || !viewingClosureData.month || !viewingClosureData.client_id) {
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Dados do fechamento inválidos'
      });
      return;
   }

   const formData = new FormData();
   formData.append('year', viewingClosureData.year);
   formData.append('month', viewingClosureData.month);
   formData.append('client_id', viewingClosureData.client_id);
   formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

   fetch(window.previewPdfRoute || '/api/monthly-closure/preview-pdf', {
      method: 'POST',
      body: formData
   })
   .then(response => {
     
      if (!response.ok) {
         return response.text().then(text => {
            console.error('Erro response:', text.substring(0, 500));
            throw new Error('Erro ao gerar PDF: HTTP ' + response.status);
         });
      }
      
      return response.blob().then(blob => {         
         if (blob.size === 0) {
            throw new Error('PDF gerado vazio');
         }
         
         if (blob.type !== 'application/pdf') {
            console.warn('Tipo de conteúdo inválido:', blob.type);
         }
         
         const url = window.URL.createObjectURL(blob);
         const pdfWindow = window.open(url, '_blank');
         
         if (!pdfWindow) {
            console.error('Não foi possível abrir a janela. Pode estar bloqueado por pop-up');
            Swal.fire({
               icon: 'warning',
               title: 'Aviso',
               text: 'A janela do PDF foi bloqueada. Verifique as configurações de pop-up.'
            });
         }
         
         setTimeout(() => window.URL.revokeObjectURL(url), 5000);
      });
   })
   .catch(error => {
      console.error('Erro ao gerar PDF:', error);
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Erro ao gerar PDF: ' + error.message
      });
   });
}

// Inicializar tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initializeTooltips, 500);
    
    // Garantir que o input de month comece vazio
    const yearMonthInput = document.getElementById('year_month');
    if (yearMonthInput) {
        yearMonthInput.value = '';
    }
    
    // Event listener para botão de abrir PDF na modal de detalhes
    const openPdfBtn = document.getElementById('openClosurePdfBtn');
    if (openPdfBtn) {
        openPdfBtn.addEventListener('click', function() {
            openClosurePdf();
        });
    }
});

// Função para gerar PDF com todos os fechamentos do mês
function printMonthlyClosures() {
    const yearMonth = document.getElementById('print_year_month').value;
    
    if (!yearMonth) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Por favor, selecione um período'
        });
        return;
    }

    const formData = new FormData();
    formData.append('year_month', yearMonth);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch(window.printPdfRoute || '/monthly-closures/counter-pdf', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.error || 'Erro ao gerar PDF');
            });
        }

        const disposition = response.headers.get('Content-Disposition');
        let filename = 'fechamentos.pdf';

        if (disposition && disposition.includes('filename=')) {
            filename = disposition.split('filename=')[1].replace(/"/g, '').trim();
        }

        return response.blob().then(blob => ({ blob, filename }));
    })
    .then(({ blob, filename }) => {
        if (blob.size === 0) {
            throw new Error('PDF gerado vazio');
        }

        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');

        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();

        Swal.fire({
            icon: 'success',
            title: 'Sucesso',
            text: 'PDF gerado com sucesso!'
        });

        // Fechar o modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('printClosureModal'));
        if (modal) {
            modal.hide();
        }

        setTimeout(() => URL.revokeObjectURL(url), 5000);
    })
    .catch(error => {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Erro ao gerar PDF'
        });
    });
}
