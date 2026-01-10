<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'my_cms');

// Create/Edit Post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['post_id'])) {
    // Update existing post
    $stmt = $db->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
    $stmt->bind_param('ssi', $_POST['title'], $_POST['content'], $_POST['post_id']);
  } else {
    // Create new post
    $stmt = $db->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->bind_param('ss', $_POST['title'], $_POST['content']);
  }
  $stmt->execute();
  header('Location: admin.php');
  exit;
}

// Delete Post
if (isset($_GET['delete'])) {
  $db->query("DELETE FROM posts WHERE id=".$_GET['delete']);
  header('Location: admin.php');
  exit;
}

// Get Post for Editing
$edit_post = null;
if (isset($_GET['edit'])) {
  $result = $db->query("SELECT * FROM posts WHERE id=".$_GET['edit']);
  $edit_post = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Simple CMS</title>
  <style>
    body { font-family: Arial; max-width: 800px; margin: 0 auto; padding: 20px; }
    .post { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
    textarea { width: 100%; height: 200px; }
  </style>
</head>
<body>
  <h1>Blog CMS</h1>

  <!-- Post Editor -->
  <form method="POST">
    <h2><?= $edit_post ? 'Edit Post' : 'New Post' ?></h2>
    <?php if ($edit_post): ?>
      <input type="hidden" name="post_id" value="<?= $edit_post['id'] ?>">
    <?php endif; ?>
    <input type="text" name="title" placeholder="Post Title"
           value="<?= $edit_post ? htmlspecialchars($edit_post['title']) : '' ?>" required>
    <br><br>
    <textarea name="content" placeholder="Post Content" required><?=
      $edit_post ? htmlspecialchars($edit_post['content']) : ''
    ?></textarea>
    <br>
    <button type="submit">Save Post</button>
  </form>

  <!-- Existing Posts -->
  <h2>All Posts</h2>
  <?php
  $posts = $db->query("SELECT * FROM posts ORDER BY created_at DESC");
  while ($post = $posts->fetch_assoc()):
  ?>
    <div class="post">
      <h3><?= htmlspecialchars($post['title']) ?></h3>
      <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
      <small>Created: <?= $post['created_at'] ?></small>
      <p>
        <a href="?edit=<?= $post['id'] ?>">Edit</a> |
        <a href="?delete=<?= $post['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
      </p>
    </div>
  <?php endwhile; ?>
</body>
</html>
