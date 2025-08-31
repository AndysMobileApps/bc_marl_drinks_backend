<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'BC Marl Drinks Admin') ?></title>
    <link href="/assets/css/admin.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php if (($page ?? '') !== 'login'): ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="/admin">
                🍺 BC Marl Drinks Admin
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>" href="/admin">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'users' ? 'active' : '' ?>" href="/admin/users">
                            <i class="bi bi-people"></i> Benutzer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'products' ? 'active' : '' ?>" href="/admin/products">
                            <i class="bi bi-box"></i> Produkte
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'transactions' ? 'active' : '' ?>" href="/admin/transactions">
                            <i class="bi bi-receipt"></i> Transaktionen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'bookings' ? 'active' : '' ?>" href="/admin/bookings">
                            <i class="bi bi-journal-bookmark"></i> Buchungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'statistics' ? 'active' : '' ?>" href="/admin/statistics">
                            <i class="bi bi-graph-up"></i> Statistiken
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin/logout"><i class="bi bi-box-arrow-right"></i> Abmelden</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="<?= ($page ?? '') !== 'login' ? 'container-fluid mt-4' : '' ?>">
        <?php include __DIR__ . '/' . ($template ?? 'admin/dashboard') . '.php'; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/admin.js"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
