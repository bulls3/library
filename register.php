<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($fullname === '' || $email === '' || $password === '') {
        $error = 'Jaza sehemu zote.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email si sahihi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password lazima iwe angalau herufi 6.';
    } elseif ($password !== $confirm) {
        $error = 'Password hazilingani.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $error = 'Email tayari imesajiliwa.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare(
                'INSERT INTO users (fullname, email, password, role) VALUES (:fullname, :email, :password, :role)'
            );
            $insert->execute([
                ':fullname' => $fullname,
                ':email' => $email,
                ':password' => $hash,
                ':role' => 'member',
            ]);
            flash('success', 'Usajili umefanikiwa. Ingia sasa.');
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jisajili — Maktaba</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand-icon">📖</div>
        <h1>Fungua akaunti</h1>
        <p class="subtitle">Jiunge na maktaba ya digital</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <div class="form-group">
                <label for="fullname">Jina kamili</label>
                <input type="text" id="fullname" name="fullname" required
                       value="<?= e($_POST['fullname'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Thibitisha password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Jisajili</button>
        </form>
        <p class="text-center mt-2">
            Tayari una akaunti? <a href="login.php">Ingia</a>
        </p>
    </div>
</div>
</body>
</html>
