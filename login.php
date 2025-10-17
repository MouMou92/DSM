<?php
session_start();

$PASSWORD = 'DSM2025';       // ← change si besoin
$INDEX_PAGE = 'index.html';  // fichier réel à afficher

// 1) Si lien QR (v=...) : laisser passer directement vers index.html avec la query string
if (isset($_GET['v'])) {
    header("Location: {$INDEX_PAGE}?" . $_SERVER['QUERY_STRING']);
    exit;
}

// 2) Si déjà authentifié, afficher le contenu de index.html (pas de redirect)
if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    // Envoi direct du contenu du fichier index.html
    if (file_exists($INDEX_PAGE)) {
        readfile($INDEX_PAGE);
        exit;
    } else {
        http_response_code(500);
        echo "Erreur serveur : fichier index introuvable.";
        exit;
    }
}

// 3) Traitement du formulaire de connexion
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['password'] ?? '');
    if ($input === $PASSWORD) {
        $_SESSION['auth'] = true;
        if (file_exists($INDEX_PAGE)) {
            readfile($INDEX_PAGE);
            exit;
        } else {
            http_response_code(500);
            echo "Erreur serveur : fichier index introuvable.";
            exit;
        }
    } else {
        $error = 'Mot de passe incorrect';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Accès privé – DSM-Consult</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{background:#0f1115;color:#e9eef5;font-family:system-ui;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.container{width:320px;text-align:center}
form{background:#161a22;padding:28px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.5)}
input[type=password]{width:100%;padding:10px;border-radius:6px;border:1px solid #333;background:#11161b;color:#fff}
button{margin-top:12px;padding:10px 18px;border-radius:8px;border:0;background:#4f8cff;color:#fff;font-weight:600;cursor:pointer}
p.error{color:#ff6b6b;margin-top:10px}
</style>
</head>
<body>
  <div class="container">
    <h1>Accès privé DSM-Consult</h1>
    <form method="post" novalidate>
      <input type="password" name="password" placeholder="Mot de passe" autofocus required>
      <button type="submit">Entrer</button>
      <?php if ($error) echo "<p class='error'>".$error."</p>"; ?>
    </form>
  </div>
</body>
</html>
