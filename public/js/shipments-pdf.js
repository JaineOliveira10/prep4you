document.addEventListener('DOMContentLoaded', function() {
    let pdfIndex = document.querySelectorAll('.pdf-upload-item').length;
    const maxPdfs = 6;

    // Adicionar PDF
    document.getElementById('add-pdf')?.addEventListener('click', function() {
        if (document.querySelectorAll('.pdf-upload-item').length >= maxPdfs) {
            Swal.fire({
                icon: 'warning',
                title: 'Limite Atingido',
                text: 'Limite máximo de 6 PDFs atingido.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const pdfUploads = document.getElementById('pdf-uploads');
        const newPdfItem = document.createElement('div');
        newPdfItem.className = 'pdf-upload-item mb-3';
        newPdfItem.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Tipo de PDF</label>
                    <select name="pdfs[${pdfIndex}][tipo]" class="form-select">
                        <option value="">Selecione o tipo</option>
                        <option value="individual_label">Etiqueta Individual</option>
                        <option value="master_label">Etiqueta Master</option>
                        <option value="invoice">Nota Fiscal</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Arquivo PDF</label>
                    <input type="file" name="pdfs[${pdfIndex}][pdf]" class="form-control" accept=".pdf">
                </div>
                <div class="col-md-2 d-flex justify-content-end align-items-end">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger d-block remove-pdf">Remover</button>
                </div>
            </div>
        `;

        pdfUploads.appendChild(newPdfItem);
        pdfIndex++;

        // Mostrar botão remover no primeiro item se houver mais de um
        updateRemoveButtons();
    });

    // Remover PDF
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-pdf')) {
            e.target.closest('.pdf-upload-item').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const items = document.querySelectorAll('.pdf-upload-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-pdf');
            if (items.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
});