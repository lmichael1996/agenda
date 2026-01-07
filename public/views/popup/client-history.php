<?php
/**
 * Popup per lo storico appuntamenti cliente - Finestra separata
 */
require_once '../../../src/Auth/AccessControl.php';

// Il file access-control.php gestisce automaticamente tutti i controlli per i popup
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storico Appuntamenti - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/scheme-popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
    <link rel="stylesheet" href="../../assets/css/client-history.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Storico Appuntamenti</span>
        </div>
        
        <div class="calendar-body">
            <!-- Client info header -->
            <div id="client-info-header">
                <h3 id="client-name-title">Caricamento...</h3>
            </div>
            
            <div class="schedules-table-container">
                <!-- Indicatore di caricamento -->
                <div id="loading-indicator">
                    <div></div>
                    <p>Caricamento appuntamenti...</p>
                </div>
                
                <table class="excel-table client-table" id="appointments-table">
                    <thead>
                        <tr>
                            <th class="th-time">Ora</th>
                            <th class="th-service">Servizio</th>
                            <th class="th-duration">Durata</th>
                            <th class="th-operator">Operatore</th>
                            <th class="th-notes">Note</th>
                        </tr>
                    </thead>
                    <tbody id="appointments-table-body">
                        <!-- HTML generato dinamicamente dal JavaScript -->
                    </tbody>
                </table>
                
                <!-- Messaggio di errore -->
                <div id="error-message">
                    <p>Errore nel caricamento dei dati</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get client ID from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const clientId = urlParams.get('clientId');
        
        // DOM Elements
        const loadingIndicator = document.getElementById('loading-indicator');
        const appointmentsTable = document.getElementById('appointments-table');
        const tableBody = document.getElementById('appointments-table-body');
        const errorMessage = document.getElementById('error-message');
        const clientNameTitle = document.getElementById('client-name-title');
        
        /**
         * Show/hide loading indicator
         */
        function showLoading() {
            loadingIndicator.style.display = 'block';
            appointmentsTable.style.display = 'none';
            errorMessage.style.display = 'none';
        }
        
        function hideLoading() {
            loadingIndicator.style.display = 'none';
        }
        
        function showTable() {
            appointmentsTable.style.display = 'table';
        }
        
        function hideTable() {
            appointmentsTable.style.display = 'none';
        }
        
        function showError(msg) {
            errorMessage.style.display = 'block';
            errorMessage.querySelector('p').textContent = msg;
            hideTable();
        }
        
        function hideError() {
            errorMessage.style.display = 'none';
        }
        
        /**
         * Fetch client details
         */
        async function fetchClientDetails(id) {
            try {
                const response = await fetch(`../../../src/Api/api.php?endpoint=clients&id=${id}`);
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Errore fetch cliente:', error);
                return { success: false, error: 'Errore di connessione' };
            }
        }
        
        /**
         * Fetch appointment history
         */
        async function fetchAppointmentHistory(clientId) {
            try {
                const response = await fetch(`../../../src/Api/api.php?endpoint=schedule&client_id=${clientId}`);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error response:', errorText);
                    throw new Error('Failed to fetch appointments');
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error loading appointment history:', error);
                return { success: false, error: error.message };
            }
        }
        
        /**
         * Format date from DD-MM-YYYY to DD/MM/YYYY
         */
        function formatDate(dateStr) {
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return `${parts[0]}/${parts[1]}/${parts[2]}`;
            }
            return dateStr;
        }
        
        /**
         * Render appointments table
         */
        function renderTable(appointments) {
            if (!tableBody) return;
            
            // Clear table
            tableBody.innerHTML = '';
            
            if (appointments.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-row';
                emptyRow.innerHTML = '<td colspan="5">Nessun appuntamento trovato per questo cliente.</td>';
                tableBody.appendChild(emptyRow);
                return;
            }
            
            // Sort by date descending (most recent first)
            appointments.sort((a, b) => {
                const dateA = new Date(a.date.split('-').reverse().join('-') + ' ' + a.time);
                const dateB = new Date(b.date.split('-').reverse().join('-') + ' ' + b.time);
                return dateB - dateA;
            });
            
            // Group appointments by date and super_appointment_id
            let currentDate = null;
            const superAppointmentGroups = {};
            
            // Group by super_appointment_id
            appointments.forEach(apt => {
                if (!superAppointmentGroups[apt.super_appointment_id]) {
                    superAppointmentGroups[apt.super_appointment_id] = [];
                }
                superAppointmentGroups[apt.super_appointment_id].push(apt);
            });
            
            // Generate rows with date headers and merged notes
            appointments.forEach((apt, index) => {
                // Add date header if date changed
                if (currentDate !== apt.date) {
                    currentDate = apt.date;
                    const dateHeaderRow = document.createElement('tr');
                    dateHeaderRow.className = 'history-date-header';
                    dateHeaderRow.innerHTML = `
                        <td colspan="5">
                            📅 ${formatDate(apt.date)}
                        </td>
                    `;
                    tableBody.appendChild(dateHeaderRow);
                }
                
                // Check if this is the first appointment in a super_appointment group
                const group = superAppointmentGroups[apt.super_appointment_id];
                const isFirstInGroup = group[0].id === apt.id;
                const groupSize = group.length;
                
                // Calculate position in group (0 = first, 1 = second, etc.)
                const positionInGroup = group.findIndex(a => a.id === apt.id);
                
                const row = createAppointmentRow(apt, isFirstInGroup, groupSize, group, positionInGroup);
                tableBody.appendChild(row);
            });
        }
        
        /**
         * Create a table row for an appointment
         */
        function createAppointmentRow(apt, showNote = true, noteRowspan = 1, group = [], positionInGroup = 0) {
            const row = document.createElement('tr');
            row.className = 'client-row';
            
            // Calculate adjusted time for appointments after the first one in the group
            let displayTime = apt.time;
            if (positionInGroup > 0 && group.length > 0) {
                // Parse start time
                const [hours, minutes] = apt.time.split(':').map(Number);
                let totalMinutes = hours * 60 + minutes;
                
                // Add duration of all previous appointments in the group
                for (let i = 0; i < positionInGroup; i++) {
                    totalMinutes += parseInt(group[i].duration) || 0;
                }
                
                // Convert back to HH:MM format
                const newHours = Math.floor(totalMinutes / 60);
                const newMinutes = totalMinutes % 60;
                displayTime = `${String(newHours).padStart(2, '0')}:${String(newMinutes).padStart(2, '0')}`;
            }
            
            // Ora cell
            const timeCell = document.createElement('td');
            timeCell.className = 'td-time';
            timeCell.textContent = displayTime;
            
            // Servizio cell
            const serviceCell = document.createElement('td');
            serviceCell.className = 'td-service';
            serviceCell.textContent = apt.service_name || '-';
            
            // Durata cell
            const durationCell = document.createElement('td');
            durationCell.className = 'td-duration';
            durationCell.textContent = apt.duration ? apt.duration + ' min' : '-';
            
            // Operatore cell
            const operatorCell = document.createElement('td');
            operatorCell.className = 'td-operator';
            operatorCell.textContent = apt.user_name || '-';
            
            row.appendChild(timeCell);
            row.appendChild(serviceCell);
            row.appendChild(durationCell);
            row.appendChild(operatorCell);
            
            // Note cell - solo per il primo appuntamento del gruppo
            if (showNote) {
                const noteCell = document.createElement('td');
                noteCell.className = 'td-notes note-cell';
                noteCell.textContent = apt.note || '-';
                noteCell.title = apt.note || '';
                if (noteRowspan > 1) {
                    noteCell.rowSpan = noteRowspan;
                }
                row.appendChild(noteCell);
            }
            
            return row;
        }
        
        /**
         * Load all data
         */
        async function loadData() {
            if (!clientId) {
                showError('ID cliente non specificato');
                return;
            }
            
            try {
                showLoading();
                hideError();
                
                // Load client details first
                const clientResponse = await fetchClientDetails(clientId);
                
                if (clientResponse.success && clientResponse.data) {
                    const client = clientResponse.data;
                    const fullName = [client.first_name, client.last_name].filter(Boolean).join(' ') || 'Cliente';
                    clientNameTitle.textContent = `Storico di ${fullName}`;
                    document.title = `Storico: ${fullName} - Agenda`;
                } else {
                    clientNameTitle.textContent = 'Cliente';
                }
                
                // Load appointments
                const appointmentsResponse = await fetchAppointmentHistory(clientId);
                
                if (appointmentsResponse.success && appointmentsResponse.appointments) {
                    renderTable(appointmentsResponse.appointments);
                    showTable();
                } else {
                    throw new Error(appointmentsResponse.error || 'Errore nel caricamento degli appuntamenti');
                }
            } catch (error) {
                console.error('Errore caricamento dati:', error);
                showError('Errore nel caricamento dei dati: ' + error.message);
            } finally {
                hideLoading();
            }
        }
        
        // Load data when page is ready
        document.addEventListener('DOMContentLoaded', () => {
            loadData();
        });
    </script>
</body>
</html>
