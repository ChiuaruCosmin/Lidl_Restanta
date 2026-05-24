<?php
header('Location: login.html');
exit;
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - UnD</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .login-box {
            max-width: 360px;
            margin: 80px auto;
            padding: 30px;
            border: 1px solid #dddddd;
            background: #fff;
        }
        .login-box h2 { margin-top: 0; }
        .login-box label { display: block; margin-bottom: 4px; font-size: 14px; }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            margin-bottom: 14px;
            box-sizing: border-box;
            border: 1px solid #ddd;
        }
        .login-box button { width: 100%; }
        .eroare { color: #c00; font-size: 14px; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Admin - UnD</h2>
    <?php if ($eroare !== ''): ?>
        <p class="eroare"><?= htmlspecialchars($eroare) ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="user">Utilizator:</label>
        <input type="text" id="user" name="user" autocomplete="off">
        <label for="pass">Parola:</label>
        <input type="password" id="pass" name="pass">
        <button type="submit">Autentificare</button>
    </form>
</div>
</body>
</html>
