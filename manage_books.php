<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Simamia Vitabu';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $active = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE book_id = ? AND status IN ('borrowed','overdue')");
            $active->execute([$id]);
            if ((int) $active->fetchColumn() > 0) {
                $error = 'Huwezi kufuta kitabu chenye mikopo hai.';
            } else {
                $pdo->prepare('DELETE FROM books WHERE id = ?')->execute([$id]);
                flash('success', 'Kitabu kimefutwa.');
                header('Location: manage_books.php');
                exit;
            }
        }
    } else {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '') ?: null;
        $category = trim($_POST['category'] ?? '') ?: 'General';
        $copies = max(1, (int) ($_POST['total_copies'] ?? 1));
        $description = trim($_POST['description'] ?? '') ?: null;

        if ($title === '' || $author === '') {
            $error = 'Kichwa na mwandishi vinahitajika.';
        } else {
            $pdo->prepare(
                'INSERT INTO books (title, author, isbn, category, total_copies, available_copies, description)
                 VALUES (:t, :a, :i, :c, :tc, :ac, :d)'
            )->execute([
                ':t' => $title,
                ':a' => $author,
                ':i' => $isbn,
                ':c' => $category,
                ':tc' => $copies,
                ':ac' => $copies,
                ':d' => $description,
            ]);
            flash('success', 'Kitabu kimeongezwa.');
            header('Location: manage_books.php');
            exit;
        }
    }
}

$books = $pdo->query('SELECT * FROM books ORDER BY title')->fetchAll();

require __DIR__ . '/includes/header.php';
$flashSuccess = flash('success');
?>

<h1>Simamia vitabu (Admin)</h1>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <h2>Ongeza kitabu kipya</h2>
    <form method="post">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
            <div class="form-group">
                <label>Kichwa cha kitabu</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Mwandishi</label>
                <input type="text" name="author" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>ISBN</label>
                <input type="text" name="isbn">
            </div>
            <div class="form-group">
                <label>Jamii</label>
                <input type="text" name="category" value="General">
            </div>
            <div class="form-group">
                <label>Idadi ya nakala</label>
                <input type="number" name="total_copies" value="1" min="1">
            </div>
        </div>
        <div class="form-group">
            <label>Maelezo</label>
            <textarea name="description" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Hifadhi</button>
    </form>
</div>

<div class="card">
    <h2>Vitabu vyote</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kichwa</th>
                    <th>Mwandishi</th>
                    <th>Nakala</th>
                    <th>Inapatikana</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= e($book['title']) ?></td>
                        <td><?= e($book['author']) ?></td>
                        <td><?= (int) $book['total_copies'] ?></td>
                        <td><?= (int) $book['available_copies'] ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Futa kitabu hiki?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Futa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
