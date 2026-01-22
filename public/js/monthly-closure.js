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

   // Buscar dados da procedure sem criar ainda
   const [year, month] = yearMonth.split('-');
   const clientName = clientIdSelect.options[clientIdSelect.selectedIndex].text;

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
         shipmentsHtml = '<table class="table table-sm table-striped"><thead><tr><th>ID</th><th>Data</th><th>Qtd</th><th>Valor</th></tr></thead><tbody>';
         data.shipments.forEach(shipment => {
            shipmentsHtml += `<tr>
               <td>${shipment.id}</td>
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

// Confirmar e criar o fechamento
function confirmNewClosure() {
   if (!newClosureData.year_month || !newClosureData.client_id) {
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Dados inválidos'
      });
      return;
   }

   // Fazer chamada POST para criar
   fetch(window.storeRoute || '/monthly-closures', {
      method: 'POST',
      headers: {
         'Content-Type': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
         year_month: newClosureData.year_month,
         client_id: newClosureData.client_id
      })
   })
   .then(response => {
      // Ler o texto primeiro para ver se é HTML ou JSON
      return response.text().then(text => {
         try {
            const jsonData = JSON.parse(text);
            return { ok: response.ok, data: jsonData };
         } catch (e) {
            // Se não conseguir parsear como JSON, retornar o texto
            console.error('Erro ao parsear JSON:', text);
            throw new Error('Resposta inválida do servidor');
         }
      });
   })
   .then(result => {
      if (!result.ok) {
         throw new Error(result.data.error || 'Erro ao criar fechamento');
      }
      
      // Fechar modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('newClosureModal'));
      modal.hide();

      // Limpar formulário
      document.getElementById('year_month').value = '';
      document.getElementById('closure_client_id').value = '';
      backToStep1();

      // Gerar PDF de preview e abrir em nova aba via fetch
      const formData = new FormData();
      formData.append('year', parseInt(newClosureData.year_month.split('-')[0]));
      formData.append('month', parseInt(newClosureData.year_month.split('-')[1]));
      formData.append('client_id', parseInt(newClosureData.client_id));
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
         
         // Obter o blob do PDF
         return response.blob().then(blob => {            
            if (blob.size === 0) {
               throw new Error('PDF gerado vazio');
            }
            
            if (blob.type !== 'application/pdf') {
               console.warn('Tipo de conteúdo inválido:', blob.type);
            }
            
            // Criar URL do blob e abrir em nova aba
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
            
            // Limpar URL após alguns segundos
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

      // Mensagem de sucesso
      Swal.fire({
         icon: 'success',
         title: 'Sucesso',
         text: 'Fechamento criado com sucesso! Abrindo PDF...',
         didClose: () => {
            location.reload();
         }
      });
   })
   .catch(error => {
      console.error('Erro:', error);
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Erro ao criar fechamento: ' + error.message
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
