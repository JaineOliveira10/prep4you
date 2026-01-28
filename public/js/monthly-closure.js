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

/**
 * Formata número no padrão português-brasileiro (com separador de milhar)
 * Exemplo: 1000 → "1.000,00"
 */
function formatarMoeda(valor) {
   if (!valor && valor !== 0) return '0,00';
   
   const num = parseFloat(valor);
   return num.toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
   });
}

function previewNewClosure() {
   const yearMonthInput = document.getElementById('modal_year_month');
   const clientIdSelect = document.getElementById('closure_client_id');
   
   let yearMonth = yearMonthInput.value || yearMonthInput.getAttribute('data-value');
   yearMonth = yearMonth ? yearMonth.trim() : '';
   const clientId = clientIdSelect.value.trim();

   if (!yearMonth) {
      Swal.fire({
         icon: 'warning',
         title: 'Validação',
         text: 'Por favor selecione ano/mês'
      });
      return;
   }

   // Armazenar dados
   newClosureData = {
      year_month: yearMonth,
      client_id: clientId ? parseInt(clientId) : null,
      closure_id: null,
      clients: [] // Array para armazenar múltiplos clientes
   };

   // Mostrar loading
   document.getElementById('loadingMessage').style.display = 'block';
   document.getElementById('step2Preview').style.display = 'none';

   // Se tem cliente específico, verificar se já existe
   if (newClosureData.client_id) {
      checkExistingClosure(yearMonth, newClosureData.client_id)
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
                     const modal = bootstrap.Modal.getInstance(document.getElementById('newClosureModal'));
                     modal.hide();
                  }
               });
               return;
            }
            
            loadClosurePreview(yearMonth);
         });
   } else {
      // Se não tem cliente, carregar para todos
      loadClosurePreview(yearMonth);
   }
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
   const clientId = newClosureData.client_id;
   const [year, month] = yearMonth.split('-');

   // Buscar dados de um ou múltiplos clientes
   fetch(`/api/monthly-closure/preview`, {
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
   .then(data => {      
      if (data.error) {
         throw new Error(data.error);
      }

      // Se é um cliente único, a resposta é um objeto
      // Se é múltiplos clientes, a resposta é um array ou objeto com múltiplas entradas
      let closures = [];
      
      if (Array.isArray(data)) {
         closures = data;
      } else if (data.shipments) {
         // É um único cliente
         closures = [data];
      } else if (data.closures) {
         // Múltiplos clientes
         closures = data.closures;
      }

      if (!closures || closures.length === 0) {
         document.getElementById('loadingMessage').style.display = 'none';
         Swal.fire({
            icon: 'warning',
            title: 'Sem Remessas',
            text: 'Nenhum cliente possui remessas neste período. Não é possível criar fechamento sem remessas.'
         });
         return;
      }

      // Armazenar dados dos clientes
      newClosureData.clients = closures;

      // Renderizar preview
      renderClosuresPreview(closures, year, month);

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

function renderClosuresPreview(closures, year, month) {
   document.getElementById('new-closure-month').textContent = `${month}/${year}`;
   
   const container = document.getElementById('closuresContainer');
   container.innerHTML = '';

   // Se há múltiplos clientes, mostrar em formato de tabela
   if (closures.length > 1) {
      let tableHtml = `
         <div class="table-responsive">
         <table class="table table-striped table-bordered">
            <thead>
               <tr>
                  <th rowspan="2" class="align-middle">Cliente</th>
                  <th colspan="2" class="text-center">Simples</th>
                  <th colspan="2" class="text-center">Kit</th>
                  <th colspan="2" class="text-center">Super Kit</th>
                  <th rowspan="2" class="text-end align-middle">Bruto R$</th>
                  <th rowspan="2" class="text-end align-middle">Desconto R$</th>
                  <th rowspan="2" class="text-end align-middle">Líquido R$</th>
               </tr>
               <tr>
                  <th class="text-center">Qtd</th>
                  <th class="text-center">Total R$</th>
                  <th class="text-center">Qtd</th>
                  <th class="text-center">Total R$</th>
                  <th class="text-center">Qtd</th>
                  <th class="text-center">Total R$</th>
               </tr>
            </thead>
            <tbody>
      `;

      closures.forEach((closureData) => {
         const clientName = closureData.client_name || closureData.client || 'Desconhecido';
         
         tableHtml += `
            <tr>
               <td><strong>${clientName}</strong></td>
               <td class="text-center">${closureData.total_simple_labels || 0}</td>
               <td class="text-center">R$ ${formatarMoeda(closureData.total_simple_net || 0)}</td>
               <td class="text-center">${closureData.total_kit_labels || 0}</td>
               <td class="text-center">R$ ${formatarMoeda(closureData.total_kit_net || 0)}</td>
               <td class="text-center">${closureData.total_superkit_labels || 0}</td>
               <td class="text-center">R$ ${formatarMoeda(closureData.total_superkit_value || 0)}</td>
               <td class="text-end"><strong>R$ ${formatarMoeda(closureData.total_gross || 0)}</strong></td>
               <td class="text-end text-danger"><strong>R$ ${formatarMoeda(closureData.total_discount || 0)}</strong></td>
               <td class="text-end text-success"><strong>R$ ${formatarMoeda(closureData.total_net || 0)}</strong></td>
            </tr>
         `;
      });

      tableHtml += `
            </tbody>
         </table>
         </div>
      `;

      // Calcular total líquido
      let totalLiquido = 0;
      closures.forEach((closure) => {
         totalLiquido += parseFloat(closure.total_net || 0);
      });

      // Adicionar totalizador
      tableHtml += `
         <div class="mt-3 p-3 rounded border">
            <div class="row">
               <div class="col-md-9 text-end">
                  <h5 class="mb-0"><strong>Total Líquido:</strong></h5>
               </div>
               <div class="col-md-3 text-end">
                  <h5 class="mb-0 text-success"><strong>R$ ${formatarMoeda(totalLiquido)}</strong></h5>
               </div>
            </div>
         </div>
      `;

      container.innerHTML = tableHtml;
   } else {
      // Se há apenas um cliente, mostrar com detalhes completos
      const closureData = closures[0];
      const clientName = closureData.client_name || closureData.client || 'Desconhecido';
      
      const closureHtml = `
         <div class="closure-card mb-4 p-3 border rounded">
            <h5 class="mb-3">
               <strong>${clientName}</strong>
            </h5>

            <div class="row mb-3">
               <div class="col-md-4">
                  <strong>Etiquetas Simples</strong>
                  <div>${closureData.total_simple_labels || 0}</div>
               </div>
               <div class="col-md-4">
                  <strong>Etiquetas Kit</strong>
                  <div>${closureData.total_kit_labels || 0}</div>
               </div>
               <div class="col-md-4">
                  <strong>Etiquetas Super Kit</strong>
                  <div>${closureData.total_superkit_labels || 0}</div>
               </div>
            </div>

            <div class="row mb-3">
               <div class="col-md-4">
                  <strong>Unitário Simples</strong>
                  <div>R$ ${formatarMoeda(closureData.unit_price_simple || 0)}</div>
               </div>
               <div class="col-md-4">
                  <strong>Unitário Kit</strong>
                  <div>R$ ${formatarMoeda(closureData.unit_price_kit || 0)}</div>
               </div>
               <div class="col-md-4">
                  <strong>Unitário Super Kit</strong>
                  <div>-</div>
               </div>
            </div>

            <div class="row mb-3">
               <div class="col-md-4">
                  <strong>Valor Simples</strong>
                  <div>R$ ${formatarMoeda(closureData.total_simple_net || 0)}</div>
               </div>
               <div class="col-md-4">
                  <strong>Valor Kit</strong>
                  <div>R$ ${formatarMoeda(closureData.total_kit_net || 0)}</div>
               </div>
               <div class="col-md-4">
                  <strong>Valor Super Kit</strong>
                  <div>R$ ${formatarMoeda(closureData.total_superkit_value || 0)}</div>
               </div>
            </div>

            <hr>

            <div class="row mb-3">
               <div class="col-md-4">
                  <strong>Valor Bruto</strong>
                  <div class="h5">R$ ${formatarMoeda(closureData.total_gross || 0)}</div>
               </div>
               <div class="col-md-4">
                  <strong>Desconto</strong>
                  <div class="h5 text-danger">R$ ${formatarMoeda(closureData.total_discount || 0)}</div>
               </div>
               <div class="col-md-4">
                  <strong>Valor Líquido</strong>
                  <div class="h5 text-success">R$ ${formatarMoeda(closureData.total_net || 0)}</div>
               </div>
            </div>

            <hr>

            <div class="row mb-3">
               <div class="col-md-4">
                  <strong>Faixa de Preço</strong>
                  <div>${closureData.price_range || '-'}</div>
               </div>
               <div class="col-md-4">
                  <strong>Desconto Simples</strong>
                  <div>R$ ${formatarMoeda(closureData.total_discount_simple || 0)}</div>
               </div>
               <div class="col-md-4">
                  <strong>Desconto Kit</strong>
                  <div>R$ ${formatarMoeda(closureData.total_discount_kit || 0)}</div>
               </div>
            </div>

            <hr>

            <strong>Remessas Incluídas:</strong>
            ${renderShipments(closureData.shipments)}
         </div>
      `;
      
      container.innerHTML = closureHtml;
   }
}

function renderShipments(shipments) {
   if (!shipments || shipments.length === 0) {
      return '<p class="text-muted mt-2">Nenhuma remessa encontrada</p>';
   }

   let html = '<table class="table table-sm table-striped mt-2"><thead><tr><th>ID Remessa</th><th>Data</th><th>Qtd</th><th>Valor</th></tr></thead><tbody>';
   
   shipments.forEach(shipment => {
      html += `<tr>
         <td>${shipment.shipment_code}</td>
         <td>${shipment.creation_date}</td>
         <td>${shipment.total_items || 0}</td>
         <td>R$ ${formatarMoeda(shipment.value || 0)}</td>
      </tr>`;
   });
   
   html += '</tbody></table>';
   return html;
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
   const yearMonth = newClosureData.year_month;
   const clientId = newClosureData.client_id;
   const clients = newClosureData.clients;

   if (!yearMonth || !clients || clients.length === 0) {
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Dados insuficientes para criar o fechamento'
      });
      return;
   }

   // Mostrar progresso
   Swal.fire({
      title: 'Processando',
      html: 'Criando fechamentos e gerando PDFs...<br><div class="progress mt-3"><div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div></div>',
      allowOutsideClick: false,
      didOpen: () => {
         Swal.showLoading();
      }
   });

   // Salvar todos os fechamentos
   saveAllClosures(0, clients);
}

function saveAllClosures(index, clients) {
   if (index >= clients.length) {
      // Todos salvos, agora gerar PDFs
      downloadAllClosuresPdfs(0, clients);
      return;
   }

   const closureData = clients[index];
   const [year, month] = newClosureData.year_month.split('-');
   const clientId = closureData.client_id || closureData.id;

   if (!clientId) {
      console.error('Client ID não encontrado para o closure', closureData);
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Client ID inválido para: ' + (closureData.client_name || closureData.client || 'Desconhecido')
      });
      return;
   }

   const formData = new FormData();
   formData.append('year_month', newClosureData.year_month);
   formData.append('client_id', clientId);
   formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

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
      // Atualizar progresso
      const progress = Math.round(((index + 1) / clients.length) * 50);
      document.getElementById('progressBar').style.width = progress + '%';
      
      // Próximo cliente
      saveAllClosures(index + 1, clients);
   })
   .catch(error => {
      console.error(error);
      Swal.hideLoading();
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: error.message || 'Erro ao criar fechamento'
      });
   });
}

function downloadAllClosuresPdfs(index, clients) {
   if (index >= clients.length) {
      // Todos os PDFs foram baixados
      Swal.fire({
         icon: 'success',
         title: 'Sucesso',
         text: 'Todos os fechamentos foram criados com sucesso!',
         didClose: () => { 
            location.reload(); 
         }
      });
      return;
   }

   const closureData = clients[index];
   const [year, month] = newClosureData.year_month.split('-');
   const clientId = closureData.client_id;

   const formData = new FormData();
   formData.append('year', parseInt(year));
   formData.append('month', parseInt(month));
   formData.append('client_id', clientId);
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

      // Baixar arquivo
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      
      setTimeout(() => URL.revokeObjectURL(url), 5000);

      // Atualizar progresso
      const progress = 50 + Math.round(((index + 1) / clients.length) * 50);
      document.getElementById('progressBar').style.width = progress + '%';

      // Próximo PDF
      downloadAllClosuresPdfs(index + 1, clients);
   })
   .catch(error => {
      console.error('Erro ao gerar PDF:', error);
      Swal.hideLoading();
      Swal.fire({
         icon: 'warning',
         title: 'Aviso',
         text: 'Fechamento criado, mas houve erro ao gerar PDF: ' + error.message
      });
      
      // Tentar próximo mesmo com erro
      downloadAllClosuresPdfs(index + 1, clients);
   });
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
         // Mostrar loading
         Swal.fire({
            title: 'Processando',
            html: 'Excluindo fechamento e atualizando remessas...',
            allowOutsideClick: false,
            didOpen: () => {
               Swal.showLoading();
            }
         });

         const url = `/monthly-closures/${closureId}/client/${clientId}`;

         // Fazer chamada AJAX para delete usando DELETE direto
         fetch(url, {
            method: 'DELETE',
            headers: {
               'Content-Type': 'application/json',
               'Accept': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
               'X-Requested-With': 'XMLHttpRequest'
            }
         })
         .then(response => {
            if (!response.ok) {
               return response.text().then(text => {
                  throw new Error('Erro ao excluir fechamento: ' + response.status);
               });
            }
            return response.json();
         })
         .then(data => {
            Swal.hideLoading();
            Swal.fire({
               icon: 'success',
               title: 'Sucesso',
               text: 'Fechamento excluído com sucesso!',
               didClose: () => {
                  location.reload();
               }
            });
         })
         .catch(error => {
            Swal.hideLoading();
            Swal.fire({
               icon: 'error',
               title: 'Erro',
               text: 'Erro ao excluir fechamento: ' + error.message
            });
         });
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
    const yearMonthInput = document.getElementById('modal_year_month');
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
// Função para visualizar fechamento
function viewClosureDetails(data) {
   const { closure_id, client_id, year, month } = data;

   if (!closure_id || !client_id || !year || !month) {
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Dados insuficientes para visualizar o fechamento'
      });
      return;
   }

   // Mostrar loading
   document.getElementById('viewClosureLoadingMessage').style.display = 'block';
   document.getElementById('viewClosureContent').style.display = 'none';

   // Abrir modal
   const modal = new bootstrap.Modal(document.getElementById('viewClosureModal'));
   modal.show();

   // Buscar dados do fechamento
   fetch(`/api/monthly-closure/preview`, {
      method: 'POST',
      headers: {
         'Content-Type': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
         year: parseInt(year),
         month: parseInt(month),
         client_id: parseInt(client_id)
      })
   })
   .then(response => response.json())
   .then(data => {
      if (data.error) {
         throw new Error(data.error);
      }

      // Obter nome do cliente
      const clientSelect = document.getElementById('closure_client_id');
      let clientName = 'N/A';
      if (clientSelect) {
         const clientOption = Array.from(clientSelect.options).find(opt => opt.value === client_id.toString());
         if (clientOption) {
            clientName = clientOption.text;
         }
      } else {
         // Se não encontrar o select, usar dados da página
         clientName = data.client_name || 'N/A';
      }

      // Preencher modal com dados
      document.getElementById('view-closure-month').textContent = `${month}/${year}`;
      document.getElementById('view-closure-client').textContent = clientName;
      document.getElementById('view-closure-simple-labels').textContent = data.total_simple_labels || 0;
      document.getElementById('view-closure-kit-labels').textContent = data.total_kit_labels || 0;
      document.getElementById('view-closure-superkit-labels').textContent = data.total_superkit_labels || 0;
      document.getElementById('view-closure-unit-simple').textContent = 'R$ ' + formatarMoeda(data.unit_price_simple || 0);
      document.getElementById('view-closure-unit-kit').textContent = 'R$ ' + formatarMoeda(data.unit_price_kit || 0);
      document.getElementById('view-closure-simple-net').textContent = 'R$ ' + formatarMoeda(data.total_simple_net || 0);
      document.getElementById('view-closure-kit-net').textContent = 'R$ ' + formatarMoeda(data.total_kit_net || 0);
      document.getElementById('view-closure-superkit-value').textContent = 'R$ ' + formatarMoeda(data.total_superkit_value || 0);
      document.getElementById('view-closure-price-range').textContent = data.price_range || '-';
      document.getElementById('view-closure-simple-discount').textContent = 'R$ ' + formatarMoeda(data.total_discount_simple || 0);
      document.getElementById('view-closure-kit-discount').textContent = 'R$ ' + formatarMoeda(data.total_discount_kit || 0);
      document.getElementById('view-closure-gross').textContent = 'R$ ' + formatarMoeda(data.total_gross || 0);
      document.getElementById('view-closure-discount').textContent = 'R$ ' + formatarMoeda(data.total_discount || 0);
      document.getElementById('view-closure-net').textContent = 'R$ ' + formatarMoeda(data.total_net || 0);;

      // Preencher remessas
      let shipmentsHtml = '';
      if (data.shipments && data.shipments.length > 0) {
         shipmentsHtml = '<table class="table table-sm table-striped"><thead><tr><th>ID Remessa</th><th>Data</th><th>Qtd</th><th>Valor</th></tr></thead><tbody>';
         data.shipments.forEach(shipment => {
            shipmentsHtml += `<tr>
               <td>${shipment.shipment_code}</td>
               <td>${shipment.creation_date}</td>
               <td>${shipment.total_items || 0}</td>
               <td>R$ ${formatarMoeda(shipment.value || 0)}</td>
            </tr>`;
         });
         shipmentsHtml += '</tbody></table>';
      } else {
         shipmentsHtml = '<p class="text-muted">Nenhuma remessa encontrada</p>';
      }
      document.getElementById('view-closure-shipments').innerHTML = shipmentsHtml;

      // Mostrar conteúdo
      document.getElementById('viewClosureLoadingMessage').style.display = 'none';
      document.getElementById('viewClosureContent').style.display = 'block';
   })
   .catch(error => {
      console.error('Erro:', error);
      document.getElementById('viewClosureLoadingMessage').style.display = 'none';
      Swal.fire({
         icon: 'error',
         title: 'Erro',
         text: 'Erro ao carregar visualização: ' + error.message
      });
   });
}