class PopupManager {
    constructor() {
        this.popups = new Map();
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        console.log('PopupManager inizializzato');
    }
    
    setupEventListeners() {
        // Gestisce tutti i click sui link con data-popup
        document.addEventListener('click', (e) => {
            const target = e.target.closest('[data-popup]');
            if (target) {
                e.preventDefault();
                const popupType = target.getAttribute('data-popup');
                this.openPopup(popupType);
            }
        });
        
        // Chiude popup quando si clicca fuori
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('popup-overlay')) {
                this.closePopup(e.target.querySelector('.popup-content').id);
            }
        });
        
        // Chiude popup con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllPopups();
            }
        });
    }
    
    openPopup(type) {
        const existingPopup = document.getElementById(`popup-${type}`);
        if (existingPopup) {
            existingPopup.style.display = 'flex';
            return;
        }
        
        const popup = this.createPopup(type);
        document.body.appendChild(popup);
        
        // Animazione di apertura
        requestAnimationFrame(() => {
            popup.style.opacity = '1';
            popup.querySelector('.popup-content').style.transform = 'scale(1)';
        });
        
        console.log(`Popup aperto: ${type}`);
    }
    
    createPopup(type) {
        const overlay = document.createElement('div');
        overlay.className = 'popup-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        const content = document.createElement('div');
        content.id = `popup-${type}`;
        content.className = 'popup-content';
        content.style.cssText = `
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            position: relative;
        `;
        
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        `;
        closeBtn.onclick = () => this.closePopup(`popup-${type}`);
        
        content.appendChild(closeBtn);
        content.appendChild(this.getPopupContent(type));
        overlay.appendChild(content);
        
        return overlay;
    }
    
    getPopupContent(type) {
        const contentDiv = document.createElement('div');
        
        switch (type) {
            case 'servizi':
                contentDiv.innerHTML = `
                    <h2>Gestione Servizi</h2>
                    <div style="margin: 1rem 0;">
                        <h3>Servizi Disponibili</h3>
                        <ul>
                            <li>Consulenza Personalizzata</li>
                            <li>Servizio Standard</li>
                            <li>Pacchetto Premium</li>
                        </ul>
                    </div>
                    <div style="margin-top: 2rem;">
                        <button onclick="alert('Nuovo servizio')" style="padding: 0.5rem 1rem; margin-right: 1rem;">Aggiungi Servizio</button>
                        <button onclick="alert('Modifica servizio')" style="padding: 0.5rem 1rem;">Modifica Servizio</button>
                    </div>
                `;
                break;
                
            case 'utenti':
                contentDiv.innerHTML = `
                    <h2>Gestione Utenti</h2>
                    <div style="margin: 1rem 0;">
                        <h3>Utenti Registrati</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="background: #f5f5f5;">
                                <th style="padding: 0.5rem; border: 1px solid #ddd;">Nome</th>
                                <th style="padding: 0.5rem; border: 1px solid #ddd;">Email</th>
                                <th style="padding: 0.5rem; border: 1px solid #ddd;">Ruolo</th>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem; border: 1px solid #ddd;">Mario Rossi</td>
                                <td style="padding: 0.5rem; border: 1px solid #ddd;">mario@email.com</td>
                                <td style="padding: 0.5rem; border: 1px solid #ddd;">Cliente</td>
                            </tr>
                        </table>
                    </div>
                    <div style="margin-top: 2rem;">
                        <button onclick="alert('Nuovo utente')" style="padding: 0.5rem 1rem; margin-right: 1rem;">Aggiungi Utente</button>
                        <button onclick="alert('Modifica utente')" style="padding: 0.5rem 1rem;">Modifica Utente</button>
                    </div>
                `;
                break;
                
            case 'orario':
                contentDiv.innerHTML = `
                    <h2>Gestione Orari</h2>
                    <div style="margin: 1rem 0;">
                        <h3>Configurazione Orari</h3>
                        <div style="margin: 1rem 0;">
                            <label>Orario apertura: <input type="time" value="08:00" style="margin-left: 1rem;"></label>
                        </div>
                        <div style="margin: 1rem 0;">
                            <label>Orario chiusura: <input type="time" value="18:00" style="margin-left: 1rem;"></label>
                        </div>
                        <div style="margin: 1rem 0;">
                            <label>Intervallo slot: 
                                <select style="margin-left: 1rem;">
                                    <option value="15">15 minuti</option>
                                    <option value="30">30 minuti</option>
                                    <option value="60">60 minuti</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div style="margin-top: 2rem;">
                        <button onclick="alert('Orari salvati')" style="padding: 0.5rem 1rem; margin-right: 1rem;">Salva Configurazione</button>
                        <button onclick="alert('Reset orari')" style="padding: 0.5rem 1rem;">Reset</button>
                    </div>
                `;
                break;
                
            default:
                contentDiv.innerHTML = `
                    <h2>Sezione: ${type}</h2>
                    <p>Contenuto per la sezione ${type} in sviluppo.</p>
                `;
        }
        
        return contentDiv;
    }
    
    closePopup(popupId) {
        const popup = document.getElementById(popupId);
        if (popup) {
            const overlay = popup.closest('.popup-overlay');
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                overlay.remove();
            }, 300);
            
            console.log(`Popup chiuso: ${popupId}`);
        }
    }
    
    closeAllPopups() {
        const overlays = document.querySelectorAll('.popup-overlay');
        overlays.forEach(overlay => {
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 300);
        });
    }
}

export { PopupManager };