<?php
/** @var array|null $user */
/** @var string $csrf */
/** @var string $widgetUrl */
/** @var string $botId */
/** @var array $menu */
/** @var array $warrantyRequests */
/** @var callable $e */
/** @var callable $json */
$chevron = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 5.5l6 6 6-6"/></svg>';
$searchIcon = '<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.414zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Dealer Portal (demo)</title>
    <link href="https://fonts.bunny.net/css?family=Nunito:400,500,600" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<?php if (!$user): ?>
    <header class="py-2 bg-white shadow-sm">
        <div class="container d-flex align-items-center gap-2"><strong>Dealer Portal</strong><span class="demo-tag">demo</span></div>
    </header>
    <main class="my-5 py-5"><div class="container"><div class="row justify-content-center"><div class="col-md-8"><div class="card">
        <div class="card-header">Inloggen</div>
        <div class="card-body">
            <?php if (isset($_GET['failed'])): ?><div class="alert alert-danger" role="alert">Onjuiste inloggegevens.</div><?php endif; ?>
            <form method="POST" action="/login">
                <input type="hidden" name="_token" value="<?= $e($csrf) ?>">
                <div class="row mb-3"><label for="email" class="col-md-4 col-form-label text-md-end">E-mailadres</label>
                    <div class="col-md-6"><input id="email" type="email" class="form-control" name="email" required autocomplete="email" autofocus></div></div>
                <div class="row mb-3"><label for="password" class="col-md-4 col-form-label text-md-end">Wachtwoord</label>
                    <div class="col-md-6"><input id="password" type="password" class="form-control" name="password" required autocomplete="current-password"></div></div>
                <div class="row mb-0"><div class="col-md-8 offset-md-4"><button type="submit" class="btn btn-primary">Inloggen</button></div></div>
            </form>
        </div>
    </div></div></div></div></main>
<?php else: ?>
<div class="admin">
    <nav class="sidebar" aria-label="Hoofdmenu">
        <div class="brand">Dealer Portal <span class="demo-tag demo-tag-dark">demo</span></div>
        <?php foreach ($menu as $item): ?><a class="item" href="#"><?= $e($item) ?><?= $chevron ?></a><?php endforeach; ?>
        <a class="item plain" href="#">PDI formulieren</a>
    </nav>
    <div class="content">
        <div class="topbar">
            <div class="dropdown">
                <a id="userMenu" class="user" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= $e($user['name']) ?></a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                    <a class="dropdown-item" id="logout-link" href="/logout">Uitloggen</a>
                    <form id="logout-form" action="/logout" method="POST" class="d-none">
                        <input type="hidden" name="_token" value="<?= $e($csrf) ?>">
                    </form>
                </div>
            </div>
        </div>
        <main class="main">
            <h2>Garantie aanvragen</h2>
            <form class="search" role="search" onsubmit="return false"><input aria-label="Zoeken op chassisnummer" placeholder="Zoeken op chassisnummer..."><button class="btn-lt" type="submit"><?= $searchIcon ?>Zoeken</button></form>
            <form class="search" role="search" onsubmit="return false"><input aria-label="Zoeken op nummer aanvraag" placeholder="Zoeken op nummer aanvrag..."><button class="btn-lt" type="submit"><?= $searchIcon ?>Zoek garantie op nummer</button></form>
            <table class="grid">
                <colgroup><col style="width:67px"><col style="width:127px"><col style="width:140px"><col style="width:195px"><col style="width:289px"><col style="width:254px"><col></colgroup>
                <thead><tr><th scope="col">#</th><th scope="col">Status</th><th scope="col">Gebruiker</th><th scope="col">Dealer</th><th scope="col">Chassisnummer</th><th scope="col">Datum aanvraag</th><th scope="col"><span class="visually-hidden">Acties</span></th></tr></thead>
                <tbody>
                <?php foreach ($warrantyRequests as $row): ?>
                    <tr><td><?= $e($row['id']) ?></td><td><span class="badge-new"><?= $e($row['status']) ?></span></td>
                        <td><?= $e($row['user']) ?></td><td><?= $e($row['dealer']) ?></td><td><?= $e($row['vin']) ?></td><td><?= $e($row['date']) ?></td>
                        <td><button class="btn-lt btn-view" type="button">Bekijken</button></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</div>
<script>
    // Laravel UI's logout pattern, plus SimplyBoost.reset() so the next user on
    // this device starts a fresh, empty chat. form.submit() does not fire the
    // form's submit event, so reset() must run here, in the click handler.
    document.getElementById('logout-link').addEventListener('click', function (event) {
        event.preventDefault();
        if (window.SimplyBoost && typeof window.SimplyBoost.reset === 'function') {
            window.SimplyBoost.reset();
        }
        document.getElementById('logout-form').submit();
    });
</script>

<?php /* ===== The integration: equivalent of Blade @auth ... @endauth ===== */ ?>
<script>
  window._CHATBOT_CONFIG_ = {
    chat_bot_id: <?= $json($botId) ?>
  };
</script>
<script>
  (function(d, s, id) {
    if (d.getElementById(id)) return;
    var js = d.createElement(s); js.id = id;
    js.src = <?= $json($widgetUrl) ?> + "?v=" + Date.now();
    js.async = true;
    d.head.appendChild(js);
  })(document, 'script', 'simplyboost-classic-script');
</script>
<?php endif; ?>
</body>
</html>
