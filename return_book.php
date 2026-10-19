<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_borrows.php');
    exit;
}

$borrowId = (int) ($_POST['borrow_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

if ($borrowId <= 0) {
    flash('error', 'Ombi si sahihi.');
    header('Location: my_borrows.php');
    exit;
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        "SELECT b.id, b.book_id, b.user_id, bk.title
         FROM borrowings b
         JOIN books bk ON bk.id = b.book_id
         WHERE b.id = :id AND b.status IN ('borrowed', 'overdue') FOR UPDATE"
    );
    $stmt->execute([':id' => $borrowId]);
    $borrow = $stmt->fetch();

    if (!$borrow) {
        throw new RuntimeException('Mkopo haukupatikana.');
    }
    if ((int) $borrow['user_id'] !== $userId && !isAdmin()) {
        throw new RuntimeException('Huna ruhusa.');
    }

    $today = date('Y-m-d');
    $upd = $pdo->prepare(
        "UPDATE borrowings SET status = 'returned', return_date = :rd WHERE id = :id"
    );
    $upd->execute([':rd' => $today, ':id' => $borrowId]);

    $bookUpd = $pdo->prepare('UPDATE books SET available_copies = available_copies + 1 WHERE id = :id');
    $bookUpd->execute([':id' => $borrow['book_id']]);

    $pdo->commit();
    flash('success', 'Umerudisha "' . $borrow['title'] . '". Asante!');
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('error', $e->getMessage());
}

header('Location: my_borrows.php');
exit;
