<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Kopa Kitabu';
$userId = (int) $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int) ($_POST['book_id'] ?? 0);
    $days = max(1, min(30, (int) ($_POST['days'] ?? 14)));

    if ($bookId <= 0) {
        $error = 'Chagua kitabu.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT id, title, available_copies FROM books WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $bookId]);
            $book = $stmt->fetch();

            if (!$book || (int) $book['available_copies'] < 1) {
                throw new RuntimeException('Kitabu hakipatikani kwa sasa.');
            }

            $existing = $pdo->prepare(
                "SELECT id FROM borrowings WHERE user_id = :uid AND book_id = :bid AND status = 'borrowed'"
            );
            $existing->execute([':uid' => $userId, ':bid' => $bookId]);
            if ($existing->fetch()) {
                throw new RuntimeException('Tayari unacho kitabu hiki.');
            }

            $borrowDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime("+{$days} days"));

            $ins = $pdo->prepare(
                'INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, status)
                 VALUES (:uid, :bid, :bd, :dd, :st)'
            );
            $ins->execute([
                ':uid' => $userId,
                ':bid' => $bookId,
                ':bd' => $borrowDate,
                ':dd' => $dueDate,
                ':st' => 'borrowed',
            ]);

            $upd = $pdo->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE id = :id');
            $upd->execute([':id' => $bookId]);

            $pdo->commit();
            flash('success', 'Umekopa "' . $book['title'] . '". Rudisha kabla ya ' . $dueDate);
            header('Location: my_borrows.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$books = $pdo->query(
    'SELECT id, title, author, available_copies FROM books WHERE available_copies > 0 ORDER BY title'
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<h1>Kopa kitabu</h1>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <?php if (empty($books)): ?>
        <p>Hakuna vitabu vinavyopatikana kwa kukopa.</p>
    <?php else: ?>
        <form method="post" action="borrow.php">
            <div class="form-group">
                <label for="book_id">Chagua kitabu</label>
                <select name="book_id" id="book_id" required>
                    <option value="">— Chagua —</option>
                    <?php foreach ($books as $b): ?>
                        <option value="<?= (int) $b['id'] ?>">
                            <?= e($b['title']) ?> — <?= e($b['author']) ?>
                            (<?= (int) $b['available_copies'] ?> zinapatikana)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="days">Siku za mkopo (max 30)</label>
                <input type="number" name="days" id="days" value="14" min="1" max="30">
            </div>
            <button type="submit" class="btn btn-success">Thibitisha mkopo</button>
        </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
