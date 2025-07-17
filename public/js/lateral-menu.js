// Sidebar hover logic: sidebar is always a "tab" partially visible, opens on hover
const sidebar = document.getElementById("sidebar");
let sidebarOpen = false;

function setSidebarTransitionOpen() {
    sidebar.style.transition = 'width 0.7s cubic-bezier(0.77,0,0.18,1)';
}

function setSidebarTransitionClose() {
    sidebar.style.transition = 'width 0.7s cubic-bezier(0.77,0,0.18,1)';
}

function openSidebar() {
    setSidebarTransitionOpen();
    sidebar.style.width = "240px";
    sidebarOpen = true;
}

function closeSidebar() {
    setSidebarTransitionClose();
    sidebar.style.width = "16px";
    sidebarOpen = false;
}

// Open on mouseenter, close on mouseleave
sidebar.addEventListener('mouseenter', openSidebar);
sidebar.addEventListener('mouseleave', closeSidebar);
// Start with sidebar closed (tab visible)
closeSidebar();