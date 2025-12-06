<?php
require_once 'db.php';

// Handle Add Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task'])) {
    $task = trim($_POST['task']);
    if (!empty($task)) {
        $stmt = $conn->prepare("INSERT INTO todos (title) VALUES (?)");
        $stmt->bind_param("s", $task);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

// Handle Delete Task
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Handle Toggle Complete
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // First check current status
    $result = $conn->query("SELECT completed FROM todos WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $new_status = $row['completed'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE todos SET completed = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

// Fetch Tasks
$result = $conn->query("SELECT * FROM todos ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Todo List</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1>Task Master</h1>

        <form action="index.php" method="POST" class="input-group">
            <input type="text" name="task" placeholder="What needs to be done?" autocomplete="off" required>
            <button type="submit" class="add-btn">Add Task</button>
        </form>

        <ul>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="<?php echo $row['completed'] ? 'completed' : ''; ?>">
                        <a href="index.php?action=toggle&id=<?php echo $row['id']; ?>" class="task-content"
                            style="text-decoration: none; color: inherit;">
                            <div class="checkbox-visual"></div>
                            <span><?php echo htmlspecialchars($row['title']); ?></span>
                        </a>
                        <a href="index.php?action=delete&id=<?php echo $row['id']; ?>" class="delete-btn"
                            onclick="return confirm('Are you sure?')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </a>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li style="text-align: center; justify-content: center; color: var(--text-muted);">
                    No tasks yet. Add one above!
                </li>
            <?php endif; ?>
        </ul>
    </div>
</body>

</html>
<?php $conn->close(); ?>