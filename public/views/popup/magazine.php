<?php
/**
 * Popup per la gestione del magazzino prodotti - Finestra separata
 * Versione organizzata basata su popup.css
 */

// Carica configurazione e controlli di sicurezza
require_once '../../../src/Auth/AccessControl.php';

// Il file access-control.php gestisce automaticamente tutti i controlli per i popup
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Magazzino - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/list-popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Magazzino Prodotti</span>
        </div>

        <div class="calendar-body">
            <div class="schedules-section">

                <div class="schedules-toolbar">
                    <button id="add-product-btn" class="toolbar-btn">Nuovo Prodotto</button>
                    <button id="delete-selected-btn" class="toolbar-btn">Elimina Selezionati</button>
                </div>

                <div class="schedules-table-container">
                    <table class="excel-table" id="products-table">
                        <thead>
                            <tr>
                                <th class="select-col"><input type="checkbox" id="select-all-products"></th>
                                <th class="product-name-col">Nome Prodotto</th>
                                <th class="price-col">Prezzo (€)</th>
                                <th class="stock-col">Giacenza</th>
                                <th class="description-col">Descrizione</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="products-list">
                            <!-- I dati vengono caricati via JS -->
                        </tbody>
                    </table>
                </div>

                <div class="services-stats">
                    <span>Totale prodotti: <strong id="total-products">0</strong></span>
                    <span>Selezionati: <strong id="selected-products">0</strong></span>
                    <span>Valore magazzino: <strong id="total-value">€ 0.00</strong></span>
                </div>

                <div class="save-btn-container">
                    <button id="save-all-btn" class="save-btn">Salva Tutti i Prodotti</button>
                </div>

            </div>
        </div>

    </div>

<script>
    // ========== GESTIONE PRODOTTI ==========
    
    let productsList = [];
    let productIdCounter = 1;

    // ========== API FUNCTIONS ==========
    
    async function loadProductsFromDb() {
        try {
            console.log('Inizio caricamento prodotti...');
            const response = await fetch('../../../src/Api/api.php?endpoint=products');
            console.log('Response status:', response.status);
            
            const text = await response.text();
            console.log('Response text:', text);
            
            if (!text || text.trim() === '') {
                console.error('Risposta vuota dal server');
                alert('Errore: risposta vuota dal server');
                return false;
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (jsonError) {
                console.error('Errore parsing JSON:', jsonError);
                console.error('Testo ricevuto:', text);
                alert('Errore parsing JSON. Vedi console per dettagli.');
                return false;
            }
            
            console.log('Dati parsati:', data);
            
            if (!data.success) {
                console.error('Errore dal server:', data.error);
                alert('Errore caricamento prodotti: ' + (data.error || 'Errore sconosciuto'));
                return false;
            }
            
            if (Array.isArray(data.products)) {
                productsList = data.products;
                console.log('Prodotti caricati:', productsList.length);
                return true;
            } else {
                console.error('Formato dati non valido:', data);
                alert('Errore: formato dati prodotti non valido');
                return false;
            }
            
        } catch (e) {
            console.error('Errore caricamento prodotti:', e);
            alert('Errore di connessione durante il caricamento dei prodotti');
            return false;
        }
    }

    async function saveProductsToDb(products) {
        try {
            const response = await fetch('../../../src/Api/api.php?endpoint=products', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ products: products })
            });
            
            const data = await response.json();
            return data;
        } catch (e) {
            console.error('Errore salvataggio prodotti:', e);
            return { success: false, error: 'Errore di connessione' };
        }
    }

    async function deleteProductFromDb(id) {
        try {
            const response = await fetch(`../../../src/Api/api.php?endpoint=products&id=${id}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            return data;
        } catch (e) {
            console.error('Errore eliminazione prodotto:', e);
            return { success: false, error: 'Errore di connessione' };
        }
    }

    // ========== UI FUNCTIONS ==========

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function generateProductRow(product) {
        const stockWarning = (product.stock_quantity || 0) <= 5 ? ' style="color: red; font-weight: bold;"' : '';
        
        return `
            <tr data-product-id="${product.id}">
                <td><input type="checkbox" class="row-select"></td>
                <td><input type="text" value="${escapeHtml(product.name)}" class="cell-input name-input"></td>
                <td><input type="number" value="${Number(product.price).toFixed(2)}" step="0.01" min="0" max="9999.99" class="cell-input price-input"></td>
                <td><input type="number" value="${product.stock_quantity || 0}" step="1" min="0" max="9999" class="cell-input stock-input"${stockWarning}></td>
                <td><textarea class="cell-textarea description-input" rows="2" placeholder="Descrizione prodotto...">${escapeHtml(product.description)}</textarea></td>
                <td class="actions-cell"><button class="action-btn btn-delete-single" data-product-id="${product.id}" title="Elimina">✘</button></td>
            </tr>
        `;
    }

    function renderProductsTable() {
        console.log('=== RENDER TABLE ===');
        const tbody = document.getElementById('products-list');
        console.log('tbody element:', tbody);
        console.log('productsList:', productsList);
        
        if (!tbody) {
            console.error('Elemento tbody non trovato!');
            return;
        }
        
        if (productsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">Nessun prodotto presente</td></tr>';
            updateSelectionStats();
            return;
        }
        
        tbody.innerHTML = productsList.map(product => generateProductRow(product)).join('');
        updateSelectionStats();
        console.log('Tabella renderizzata con', productsList.length, 'prodotti');
    }

    function updateSelectionStats() {
        const totalProducts = productsList.length;
        const selectedProducts = document.querySelectorAll('.row-select:checked').length;
        const totalValue = productsList.reduce((sum, p) => sum + (Number(p.price) * Number(p.stock_quantity || 0)), 0);
        
        document.getElementById('total-products').textContent = totalProducts;
        document.getElementById('selected-products').textContent = selectedProducts;
        document.getElementById('total-value').textContent = '€ ' + totalValue.toFixed(2);
    }

    function syncRowToModel(row, id) {
        const product = productsList.find(p => p.id == id);
        if (!product) return true;
        
        // Selettori specifici per ogni campo
        const nameInput = row.querySelector('.name-input');
        const priceInput = row.querySelector('.price-input');
        const stockInput = row.querySelector('.stock-input');
        const descriptionInput = row.querySelector('.description-input');
        
        const name = nameInput?.value?.trim() || '';
        const price = parseFloat(priceInput?.value) || 0;
        const stock = parseInt(stockInput?.value) || 0;
        const description = descriptionInput?.value?.trim() || '';
        
        // Validazione nome obbligatorio
        if (!name) {
            nameInput.classList.add('error-highlight');
            return false;
        } else {
            nameInput.classList.remove('error-highlight');
        }
        
        // Aggiorna modello
        product.name = name;
        product.price = price;
        product.stock_quantity = stock;
        product.description = description;
        
        return true;
    }

    // ========== EVENT HANDLERS ==========

    function onAddProduct() {
        const newProduct = {
            id: 'temp_' + Date.now(),
            name: 'Nuovo Prodotto ' + (productsList.length + 1),
            price: 0.00,
            stock_quantity: 0,
            description: ''
        };
        
        productsList.push(newProduct);
        renderProductsTable();
        
        setTimeout(() => {
            const newRow = document.querySelector(`tr[data-product-id="${newProduct.id}"] input[type="text"]`);
            if (newRow) newRow.focus();
        }, 0);
    }

    function onDeleteSingle(id) {
        const product = productsList.find(p => p.id == id);
        if (!product) return;

        if (!confirm(`Rimuovere prodotto "${product.name}" dalla lista?\n\nNOTA: La modifica sarà effettiva solo dopo aver premuto "Salva Tutti i Prodotti".`)) return;
        
        // Rimuove il prodotto solo dalla lista locale (non dal database)
        productsList = productsList.filter(p => p.id != id);
        renderProductsTable();
    }

    function onDeleteSelected() {
        const selectedCheckboxes = document.querySelectorAll('.row-select:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Nessun prodotto selezionato');
            return;
        }
        
        if (!confirm(`Rimuovere ${selectedCheckboxes.length} prodotto/i selezionato/i dalla lista?\n\nNOTA: La modifica sarà effettiva solo dopo aver premuto "Salva Tutti i Prodotti".`)) return;
        
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.closest('tr').dataset.productId);
        
        // Rimuove i prodotti solo dalla lista locale (non dal database)
        productsList = productsList.filter(p => !selectedIds.includes(String(p.id)));
        
        renderProductsTable();
        document.getElementById('select-all-products').checked = false;
    }

    async function onSaveAll() {
        console.log('=== onSaveAll chiamata ===');
        
        // Sincronizza tutti i dati dalla UI
        let allValid = true;
        document.querySelectorAll('#products-list tr[data-product-id]').forEach(row => {
            const productId = row.dataset.productId;
            const isValid = syncRowToModel(row, productId);
            if (!isValid) allValid = false;
        });
        
        console.log('Validazione:', allValid);
        
        if (!allValid) {
            alert('Correggi gli errori evidenziati prima di salvare');
            return;
        }
        
        console.log('productsList.length:', productsList.length);
        console.log('productsList:', productsList);
        
        if (productsList.length === 0) {
            alert('Nessun prodotto da salvare');
            return;
        }
        
        console.log('Mostrando conferma...');
        const confirmed = confirm(`Aggiorna la lista prodotti nel database?\n\nATTENZIONE: Questa operazione aggiornerà tutti i prodotti esistenti.`);
        console.log('Confermato:', confirmed);
        
        if (!confirmed) {
            console.log('Operazione annullata dall\'utente');
            return;
        }
        
        console.log('Inizio salvataggio...');
        const saveBtn = document.getElementById('save-all-btn');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Salvataggio...';
        saveBtn.disabled = true;
        
        try {
            console.log('Chiamando saveProductsToDb...');
            const result = await saveProductsToDb(productsList);
            console.log('Risultato ricevuto:', result);
            
            if (result.success) {
                alert('Prodotti salvati con successo!');
                console.log('Ricaricando prodotti...');
                await loadProductsFromDb();
                renderProductsTable();
                
                setTimeout(() => {
                    console.log('Chiudendo finestra...');
                    window.close();
                }, 500);
            } else {
                console.error('Errore dal server:', result.error);
                console.error('Dettagli errore:', result.details);
                console.error('Risposta completa:', result);
                
                let errorMsg = 'Errore durante il salvataggio:\n' + (result.error || 'Errore sconosciuto');
                if (result.details) {
                    errorMsg += '\n\nDettagli:\n' + result.details;
                }
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Eccezione durante salvataggio:', error);
            alert('Errore critico durante il salvataggio. Vedi console per dettagli.');
        } finally {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    }

    // ========== INITIALIZATION ==========

    document.addEventListener('DOMContentLoaded', async () => {
        console.log('=== DOM LOADED ===');
        console.log('Caricamento prodotti...');
        
        const loaded = await loadProductsFromDb();
        console.log('Dati caricati:', loaded);
        console.log('productsList length:', productsList.length);
        console.log('productsList:', productsList);
        
        renderProductsTable();
        console.log('Tabella renderizzata');
        
        // Event listeners pulsanti
        document.getElementById('add-product-btn')?.addEventListener('click', onAddProduct);
        document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected);
        document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll);
        
        // Event listener select all
        document.getElementById('select-all-products')?.addEventListener('change', e => {
            const checked = e.target.checked;
            document.querySelectorAll('.row-select').forEach(cb => cb.checked = checked);
            updateSelectionStats();
        });
        
        // Event listener checkbox individuali e giacenza
        document.addEventListener('change', e => {
            if (e.target.classList.contains('row-select')) {
                updateSelectionStats();
            }
            
            // Gestione cambio giacenza per evidenziare scorte basse
            if (e.target.classList.contains('stock-input')) {
                const stock = parseInt(e.target.value) || 0;
                if (stock <= 5) {
                    e.target.style.color = 'red';
                    e.target.style.fontWeight = 'bold';
                } else {
                    e.target.style.color = '';
                    e.target.style.fontWeight = '';
                }
                updateSelectionStats();
            }
        });
        
        // Event listener per aggiornamento valore totale quando cambia prezzo
        document.addEventListener('input', e => {
            if (e.target.classList.contains('price-input') || e.target.classList.contains('stock-input')) {
                updateSelectionStats();
            }
        });
        
        // Event listener delete singolo
        document.addEventListener('click', e => {
            const deleteBtn = e.target.closest('.btn-delete-single');
            if (deleteBtn) {
                const id = deleteBtn.dataset.productId;
                onDeleteSingle(id);
            }
        });
        
        console.log('Event listeners registrati');
    });

</script>

<style>
    /* Colonne specifiche */
    .product-name-col {
        width: 30%;
        min-width: 200px;
    }

    .price-col {
        width: 12%;
        min-width: 100px;
    }

    .stock-col {
        width: 12%;
        min-width: 80px;
    }

    .description-col {
        width: 40%;
        min-width: 250px;
    }

    .error-highlight {
        border: 2px solid red !important;
        background-color: #ffe6e6 !important;
    }
</style>

</body>
</html>
