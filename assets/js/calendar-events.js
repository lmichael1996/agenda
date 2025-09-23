import { WorkspaceController } from './classes/workspace-controller.js';

// Inizializzazione automatica quando il DOM è pronto
document.addEventListener('DOMContentLoaded', () => {
    window.workspaceController = new WorkspaceController();
});