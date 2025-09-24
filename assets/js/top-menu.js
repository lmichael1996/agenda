document.getElementById('note-btn').addEventListener('click', function() {
    const w = 1600, h = 900;
    const left = window.screenX + (window.outerWidth - w) / 2;
    const top = window.screenY + (window.outerHeight - h) / 2;
    window.open('popup/note.php', 'Gestione Nota', `width=${w},height=${h},left=${left},top=${top},scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no`);
});

document.getElementById('client-search').addEventListener('click', function() {
    const w = 1600, h = 900;
    const left = window.screenX + (window.outerWidth - w) / 2;
    const top = window.screenY + (window.outerHeight - h) / 2;
    window.open('popup/client.php', 'Gestione Cliente', `width=${w},height=${h},left=${left},top=${top},scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no`);
});