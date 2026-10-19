<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Jaza email na password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, fullname, email, password, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            flash('success', 'Karibu, ' . $user['fullname'] . '!');
            header('Location: index.php');
            exit;
        }
        $error = 'Email au password si sahihi.';
    }
}

$success = flash('success');
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingia — Maktaba</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand-icon">📚</div>
        <h1>Ingia kwenye Maktaba</h1>
        <p class="subtitle">Weka email na password yako</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Ingia</button>
        </form>
        <p class="text-center mt-2">
            Huna akaunti? <a href="register.php">Jisajili hapa</a>
        </p>
        <p class="text-center mt-2" style="font-size: 0.8rem; color: var(--muted);">
            Admin mfano: admin@library.local / admin123
        </p>
    </div>
</div>
</body>
</html>
