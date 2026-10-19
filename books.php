<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Orodha ya Vitabu';

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM books WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (title LIKE :q OR author LIKE :q OR category LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}
$sql .= ' ORDER BY title ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="flex-between">
    <h1>Vitabu</h1>
    <form method="get" action="books.php" style="display: flex; gap: 0.5rem;">
        <input type="text" name="q" placeholder="Tafuta..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-primary">Tafuta</button>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kichwa</th>
                    <th>Mwandishi</th>
                    <th>Jamii</th>
                    <th>ISBN</th>
                    <th>Inapatikana</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= (int) $book['id'] ?></td>
                        <td><?= e($book['title']) ?></td>
                        <td><?= e($book['author']) ?></td>
                        <td><?= e($book['category']) ?></td>
                        <td><?= e($book['isbn'] ?? '—') ?></td>
                        <td>
                            <?php if ((int) $book['available_copies'] > 0): ?>
                                <span class="badge badge-success"><?= (int) $book['available_copies'] ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger">0</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($books)): ?>
                    <tr><td colspan="6">Hakuna vitabu vilivyopatikana.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
