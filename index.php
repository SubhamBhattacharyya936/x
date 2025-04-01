<?php
// Database connection
$host = 'localhost';
$user = 'root'; // Change if needed
$pass = ''; // Change if needed
$dbname = 'datacollection';
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// INSERT DATA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $name = $_POST['name'];
    $title = $_POST['title'];

    $stmt = $conn->prepare("INSERT INTO nametitle (name, title) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $title);

    if ($stmt->execute()) {
        header("Location: index.php?msg=inserted");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// DELETE DATA
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $stmt = $conn->prepare("DELETE FROM nametitle WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// EDIT DATA - Fetch for editing
$editData = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);

    $stmt = $conn->prepare("SELECT * FROM nametitle WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
}

// UPDATE DATA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $title = $_POST['title'];

    $stmt = $conn->prepare("UPDATE nametitle SET name = ?, title = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $title, $id);

    if ($stmt->execute()) {
        header("Location: index.php?msg=updated");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch index data for display
$sql = "SELECT * FROM nametitle ORDER BY id DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Collection</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; }
        .container { width: 90%; max-width: 600px; padding: 20px; margin-top: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        input, button { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #f4f4f4; }
        a { text-decoration: none; color: white; padding: 5px 10px; border-radius: 5px; }
        .edit-btn { background: #007bff; }
        .delete-btn { background: #dc3545; }
        .edit-btn:hover { background: #0056b3; }
        .delete-btn:hover { background: #c82333; }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo $editData ? "Edit Data" : "Enter Your Details"; ?></h2>
    <form method="POST">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
        <?php endif; ?>
        <input type="text" name="name" placeholder="Enter Name" value="<?php echo $editData['name'] ?? ''; ?>" required>
        <input type="text" name="title" placeholder="Enter Title" value="<?php echo $editData['title'] ?? ''; ?>" required>
        <button type="submit" name="<?php echo $editData ? 'update' : 'submit'; ?>">
            <?php echo $editData ? "Update" : "Submit"; ?>
        </button>
        <?php if ($editData): ?>
            <a href="index.php">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="container">
    <h2>Submitted Data</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Title</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td>
                    <a class="edit-btn" href="index.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    <a class="delete-btn" href="index.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
