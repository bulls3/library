<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Dashibodi';

$bookCount = (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$memberCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$activeBorrows = (int) $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed'")->fetchColumn();

$userId = (int) $_SESSION['user_id'];
$myBorrows = (int) $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status = 'borrowed'");
$myBorrows->execute([$userId]);
$myBorrowCount = (int) $myBorrows->fetchColumn();

$recentBooks = $pdo->query(
    'SELECT title, author, available_copies FROM books ORDER BY created_at DESC LIMIT 5'
)->fetchAll();

require __DIR__ . '/includes/header.php';

$flashSuccess = flash('success');
$flashError = flash('error');
?>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-error"><?= e($flashError) ?></div>
<?php endif; ?>

<h1 style="margin-bottom: 1rem;">Karibu, <?= e($_SESSION['fullname']) ?></h1>

<div class="stats">
    <div class="stat-card">
        <h3><?= $bookCount ?></h3>
        <p>Vitabu kwenye maktaba</p>
    </div>
    <div class="stat-card">
        <h3><?= $memberCount ?></h3>
        <p>Wanachama</p>
    </div>
    <div class="stat-card">
        <h3><?= $activeBorrows ?></h3>
        <p>Mikopo hai (wote)</p>
    </div>
    <div class="stat-card">
        <h3><?= $myBorrowCount ?></h3>
        <p>Vitabu ulivyokopa</p>
    </div>
</div>

<div class="card">
    <h2>Vitabu vya hivi karibuni</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kichwa</th>
                    <th>Mwandishi</th>
                    <th>Inapatikana</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentBooks)): ?>
                    <tr><td colspan="3">Hakuna vitabu bado.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentBooks as $book): ?>
                        <tr>
                            <td><?= e($book['title']) ?></td>
                            <td><?= e($book['author']) ?></td>
                            <td><?= (int) $book['available_copies'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="mt-2"><a href="borrow.php" class="btn btn-primary">Kopa kitabu sasa</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
