<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/top-menu.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/calendar-events.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/week-calendar-style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/lateral-menu-style.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php setSecurityHeaders(); ?>
    
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar sidebar-hover">
        <a href="#">Servizi</a>
        <a href="#">Utenti</a>
        <a href="#">Orario</a>
    </div>

    <!-- Top Menu Controls -->
    <div class="dashboard-controls">
        <div class="controls-left">
            <select id="set-view" onmouseover="this.style.cursor='pointer'">
                <option value="week">Settimana</option>
                <option value="day">Giorno</option>
            </select>
            <input type="week" id="set-week" onmouseover="this.style.cursor='pointer'" value="<?= (new DateTime())->format('Y-\WW') ?>">
            <input type="date" id="set-date" onmouseover="this.style.cursor='pointer'" value="<?= (new DateTime())->format('Y-m-d') ?>" hidden>
        </div>
        
        <div class="controls-center">
            <label for="cerca" class="control-label">Cliente:</label>
            <input type="search" autocomplete="on" id="cerca" placeholder="Cerca cliente...">
            <input type="submit" onmouseover="this.style.cursor='pointer'" value="Cerca">
            <button type="button" id="note-btn" title="Note" onmouseover="this.style.cursor='pointer'">📝</button>
        </div>
        
        <div class="controls-right">
            <marquee> Non è mai troppo tardi per essere ciò che vuoi essere! </marquee>
        </div>
    </div>

    <!-- Main Content -->
    <main class="dashboard-container">
        <?= $content ?? '' ?>
    </main>

    <!-- JavaScript -->
    <script src="<?= ASSETS_URL ?>/js/lateral-menu.js"></script>
    <script src="<?= ASSETS_URL ?>/js/calendar-date.js"></script>
    <script src="<?= ASSETS_URL ?>/js/calendar-events.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= ASSETS_URL ?>/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>