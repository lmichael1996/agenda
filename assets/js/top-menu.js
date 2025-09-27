document.getElementById('note-btn').addEventListener('click', function() {
    const w = 1600, h = 900;
    const left = window.screenX + (window.outerWidth - w) / 2;
    const top = window.screenY + (window.outerHeight - h) / 2;
    window.open('popup/note.php', 'Gestione Nota', `width=${w},height=${h},left=${left},top=${top},scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no`);
});

document.getElementById('client-search').addEventListener('click', function() {
    // Ottieni i valori di ricerca dal dashboard
    const searchText = document.getElementById('cerca').value.trim();
    const searchField = document.getElementById('search-field-select').value;
    
    // Costruisci URL con parametri di ricerca
    let url = 'popup/client.php';
    if (searchText || searchField !== 'name') {
        const params = new URLSearchParams();
        if (searchText) params.set('search', searchText);
        if (searchField !== 'name') params.set('searchField', searchField);
        url += '?' + params.toString();
    }
    
    const w = 1600, h = 900;
    const left = window.screenX + (window.outerWidth - w) / 2;
    const top = window.screenY + (window.outerHeight - h) / 2;
    window.open(url, 'Gestione Cliente', `width=${w},height=${h},left=${left},top=${top},scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no`);
});