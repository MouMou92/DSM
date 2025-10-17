<?php
session_start();

$INDEX_PAGE = 'index.html';
$STORAGE_KEY = 'espace-memoire-auth';
$PASSWORD_HASH = '$2y$12$uA5pB04YQiZCXjlwd6KzVu3C18PpiqhKKe645gNEJ4fARv0IxwgSO'; // hash de "DSM2025"
$CLIENT_HASH = 'aa01ad3c84d1519104cf71b88da94e7d9f13b74286150fd86152874ddd8460d1'; // SHA-256

// 1) Si lien QR (?id=...), laisser passer directement vers l'espace public.
if (isset($_GET['id'])) {
    $query = $_SERVER['QUERY_STRING'];
    header("Location: {$INDEX_PAGE}" . ($query ? "?{$query}" : ''));
    exit;
}

// 2) Si déjà authentifié côté serveur, injecter directement la sessionStorage et rediriger.
if (!empty($_SESSION['auth'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo redirectWithStorage($INDEX_PAGE, $STORAGE_KEY, $CLIENT_HASH);
    exit;
}

// 3) Traitement du formulaire de connexion
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['password'] ?? '');
    if (password_verify($input, $PASSWORD_HASH)) {
        $_SESSION['auth'] = true;
        header('Content-Type: text/html; charset=utf-8');
        echo redirectWithStorage($INDEX_PAGE, $STORAGE_KEY, $CLIENT_HASH);
        exit;
    }
    $error = 'Mot de passe incorrect';
}

function redirectWithStorage(string $target, string $key, string $value): string
{
    $targetEscaped = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
    $keyEscaped = json_encode($key, JSON_UNESCAPED_SLASHES);
    $valueEscaped = json_encode($value, JSON_UNESCAPED_SLASHES);

    if ($keyEscaped === false || $valueEscaped === false) {
        $keyEscaped = '"' . addslashes($key) . '"';
        $valueEscaped = '"' . addslashes($value) . '"';
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion réussie – Redirection</title>
  <meta http-equiv="refresh" content="1;url={$targetEscaped}">
  <script>
    try {
      sessionStorage.setItem({$keyEscaped}, {$valueEscaped});
    } catch (error) {
      console.error('Impossible d\'initialiser la session protégée.', error);
    }
    window.location.replace('{$targetEscaped}');
  </script>
</head>
<body>
  <p>Connexion réussie. Redirection…</p>
</body>
</html>
HTML;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Espace mémoire – Connexion</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{color-scheme:dark;--bg:#0f0f10;--fg:#e0e0e0;--accent:#90caf9;--panel:rgba(28,28,30,.78);--border:rgba(255,255,255,.08)}
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;background:var(--bg);color:var(--fg);font-family:"Segoe UI",Roboto,system-ui,sans-serif;display:flex;align-items:center;justify-content:center;padding:48px 16px}
    main{width:min(420px,100%)}
    .panel{background:var(--panel);border:1px solid var(--border);border-radius:20px;padding:36px 28px;box-shadow:0 18px 48px rgba(0,0,0,.45);backdrop-filter:blur(6px)}
    h1{margin:0 0 12px;color:var(--accent);font-size:clamp(1.6rem,2vw+1rem,2.4rem);text-align:center}
    p{margin:0 0 24px;line-height:1.5;text-align:center}
    form{display:grid;gap:18px}
    input[type=password]{width:100%;padding:14px 16px;border-radius:12px;border:1px solid var(--border);background:rgba(12,12,14,.85);color:var(--fg);font-size:1rem}
    button{border:none;border-radius:999px;padding:12px 22px;font-size:1rem;font-weight:600;letter-spacing:.01em;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,opacity .15s ease}
    button.primary{background:linear-gradient(135deg,#1e88e5,#42a5f5);color:#fff;box-shadow:0 12px 32px rgba(66,165,245,.42)}
    button.primary:hover{transform:translateY(-2px);box-shadow:0 18px 40px rgba(66,165,245,.5)}
    .error{color:#ff8a80;text-align:center;min-height:1.5em;font-weight:600}
    footer{margin-top:18px;text-align:center;font-size:.85rem;opacity:.7}
    footer a{color:var(--accent);text-decoration:none}
    footer a:hover{text-decoration:underline}
  </style>
</head>
<body>
  <main>
    <section class="panel">
      <h1>Espace mémoire</h1>
      <p>Entrez le mot de passe communiqué par DSM-Consult pour accéder aux capsules vidéo.</p>
      <form method="post" novalidate>
        <input type="password" name="password" placeholder="Mot de passe" autocomplete="current-password" autofocus required>
        <button type="submit" class="primary">Se connecter</button>
        <div class="error" role="alert" aria-live="assertive"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      </form>
      <footer>
        <a href="<?php echo htmlspecialchars($INDEX_PAGE, ENT_QUOTES, 'UTF-8'); ?>">Accéder via un QR code</a>
      </footer>
    </section>
  </main>
</body>
</html>
