<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Mikopo Yangu';
$userId = (int) $_SESSION['user_id'];

// Mark overdue
$pdo->exec("UPDATE borrowings SET status = 'overdue'
    WHERE status = 'borrowed' AND due_date < CURDATE()");

$stmt = $pdo->prepare(
    'SELECT b.id AS borrow_id, bk.title, bk.author, b.borrow_date, b.due_date, b.return_date, b.status
     FROM borrowings b
     JOIN books bk ON bk.id = b.book_id
     WHERE b.user_id = :uid
     ORDER BY b.created_at DESC'
);
$stmt->execute([':uid' => $userId]);
$rows = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';

$flashSuccess = flash('success');
?>

<h1>Mikopo yangu</h1>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success"><?= e($flashSuccess) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kitabu</th>
                    <th>Mwandishi</th>
                    <th>Ilikopwa</th>
                    <th>Mwisho</th>
                    <th>Hali</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['title']) ?></td>
                        <td><?= e($row['author']) ?></td>
                        <td><?= e($row['borrow_date']) ?></td>
                        <td><?= e($row['due_date']) ?></td>
                        <td>
                            <?php
                            $st = $row['status'];
                            $class = $st === 'returned' ? 'badge-muted' : ($st === 'overdue' ? 'badge-danger' : 'badge-warning');
                            ?>
                            <span class="badge <?= $class ?>"><?= e($st) ?></span>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'borrowed' || $row['status'] === 'overdue'): ?>
                                <form method="post" action="return_book.php" style="display:inline;">
                                    <input type="hidden" name="borrow_id" value="<?= (int) $row['borrow_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Rudisha</button>
                                </form>
                            <?php else: ?>
                                <?= e($row['return_date'] ?? '—') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6">Hujakopa kitabu bado. <a href="borrow.php">Kopa sasa</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
