// Servizi semplificati
let services = [
    { name: "Servizio Base", price: 30, duration: 60, status: "attivo" }
];

// Aggiungi nuovo servizio
function addNewService() {
    const tbody = document.getElementById('services-tbody');
    const newRow = `
        <tr>
            <td><input type="checkbox" class="row-select"></td>
            <td><input type="text" value="Nuovo Servizio" class="cell-input"></td>
            <td><input type="number" value="30" step="5" min="0" class="cell-input"></td>
            <td><input type="number" value="60" step="15" min="15" class="cell-input"></td>
            <td>
                <select class="cell-select">
                    <option value="attivo" selected>Attivo</option>
                    <option value="inattivo">Inattivo</option>
                </select>
            </td>
            <td><button onclick="deleteRow(this)" class="btn-delete">🗑️</button></td>
        </tr>
    `;
    tbody.insertAdjacentHTML('beforeend', newRow);
}

// Elimina servizi selezionati
function deleteSelectedServices() {
    const selected = document.querySelectorAll('.row-select:checked');
    if (selected.length === 0) {
        alert('Nessun servizio selezionato');
        return;
    }
    if (confirm(`Eliminare ${selected.length} servizi?`)) {
        selected.forEach(checkbox => checkbox.closest('tr').remove());
    }
}

// Elimina singola riga
function deleteRow(button) {
    if (confirm('Eliminare questo servizio?')) {
        button.closest('tr').remove();
    }
}

// Seleziona/deseleziona tutti
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-select');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// Salva tutto
function saveAll() {
    alert('Servizi salvati!');
}