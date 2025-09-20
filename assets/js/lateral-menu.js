const sidebar = document.getElementById("sidebar");

function openSidebar() {
    sidebar.style.transition = 'width 0.7s cubic-bezier(0.77,0,0.18,1)';
    sidebar.style.width = "240px";
}

function closeSidebar() {
    sidebar.style.transition = 'width 0.7s cubic-bezier(0.77,0,0.18,1)';
    sidebar.style.width = "16px";
}

// Open on mouseenter, close on mouseleave
sidebar.addEventListener('mouseenter', openSidebar);
sidebar.addEventListener('mouseleave', closeSidebar);

// Start with sidebar closed (tab visible)
closeSidebar();