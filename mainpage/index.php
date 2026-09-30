<?php
session_start();
include('../includes/db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = '';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function iniBytes(string $value): int
{
    $value = trim($value);
    $unit = strtolower(substr($value, -1));
    $number = (float) $value;

    if ($unit === 'g') {
        $number *= 1024;
    }
    if ($unit === 'm') {
        $number *= 1024;
    }
    if ($unit === 'k') {
        $number *= 1024;
    }

    return (int) $number;
}

function uploadImage(): array
{
    if (
        !isset($_FILES['imgcontent']) ||
        $_FILES['imgcontent']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return [null, ''];
    }

    if ($_FILES['imgcontent']['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The image is larger than PHP allows. Maximum size: '
                . ini_get('upload_max_filesize') . '.',
            UPLOAD_ERR_FORM_SIZE => 'The image is larger than the form allows.',
            UPLOAD_ERR_PARTIAL => 'The image upload was incomplete.',
            UPLOAD_ERR_NO_TMP_DIR => 'PHP temporary upload folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'PHP cannot write the uploaded image to the temporary folder.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the image upload.'
        ];

        return [
            null,
            $uploadErrors[$_FILES['imgcontent']['error']] ?? 'Unknown image upload error.'
        ];
    }

    $maxUploadSize = iniBytes(ini_get('upload_max_filesize'));
    if ($_FILES['imgcontent']['size'] > $maxUploadSize) {
        return [null, 'The image must be ' . ini_get('upload_max_filesize') . ' or smaller.'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $imageType = mime_content_type($_FILES['imgcontent']['tmp_name']);

    if (!in_array($imageType, $allowedTypes, true)) {
        return [null, 'Please upload a JPG, PNG, GIF, or WEBP image.'];
    }

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $imageName = bin2hex(random_bytes(16)) . '.' . $extensions[$imageType];
    $uploadPath = __DIR__ . '/../imgstorage/' . $imageName;

    if (!move_uploaded_file($_FILES['imgcontent']['tmp_name'], $uploadPath)) {
        return [null, 'The image could not be saved.'];
    }

    return [$imageName, ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

    if ($action === 'create') {
        $content = trim($_POST['content'] ?? '');

        if ($content === '') {
            $message = 'Please write something before posting.';
        } else {
            [$imageName, $message] = uploadImage();
        }

        if ($content !== '' && $message === '') {
            $stmt = $dbconn->prepare(
                'INSERT INTO vlog_posts (TITLE, IMAGE, AUTHOR_ID) VALUES (?, ?, ?)'
            );
            $stmt->bind_param('ssi', $content, $imageName, $userId);
            $message = $stmt->execute()
                ? 'Post published successfully.'
                : 'Post could not be published: ' . $stmt->error;
            $stmt->close();
        }
    } elseif ($action === 'delete' && $postId) {
        $stmt = $dbconn->prepare(
            'SELECT IMAGE FROM vlog_posts WHERE PID = ? AND AUTHOR_ID = ?'
        );
        $stmt->bind_param('ii', $postId, $userId);
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$post) {
            $message = 'You can only delete your own posts.';
        } else {
            $stmt = $dbconn->prepare(
                'DELETE FROM vlog_posts WHERE PID = ? AND AUTHOR_ID = ?'
            );
            $stmt->bind_param('ii', $postId, $userId);
            $message = $stmt->execute()
                ? 'Post deleted successfully.'
                : 'Post could not be deleted: ' . $stmt->error;
            $stmt->close();

            if ($message === 'Post deleted successfully.' && !empty($post['IMAGE'])) {
                $imagePath = __DIR__ . '/../imgstorage/' . basename($post['IMAGE']);
                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    } elseif ($action === 'edit' && $postId) {
        $content = trim($_POST['content'] ?? '');

        if ($content === '') {
            $message = 'Post content cannot be empty.';
        } else {
            $stmt = $dbconn->prepare(
                'SELECT IMAGE FROM vlog_posts WHERE PID = ? AND AUTHOR_ID = ?'
            );
            $stmt->bind_param('ii', $postId, $userId);
            $stmt->execute();
            $post = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$post) {
                $message = 'You can only edit your own posts.';
            } else {
                [$newImage, $uploadMessage] = uploadImage();
                if ($uploadMessage !== '') {
                    $message = $uploadMessage;
                } else {
                    $imageName = $newImage ?: $post['IMAGE'];
                    $stmt = $dbconn->prepare(
                        'UPDATE vlog_posts SET TITLE = ?, IMAGE = ?
                         WHERE PID = ? AND AUTHOR_ID = ?'
                    );
                    $stmt->bind_param('ssii', $content, $imageName, $postId, $userId);
                    $message = $stmt->execute()
                        ? 'Post updated successfully.'
                        : 'Post could not be updated: ' . $stmt->error;
                    $stmt->close();

                    if ($newImage && !empty($post['IMAGE'])) {
                        $oldImagePath = __DIR__ . '/../imgstorage/' . basename($post['IMAGE']);
                        if (is_file($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }
            }
        }
    }
}

$stmt = $dbconn->prepare(
    'SELECT vlog_posts.PID, vlog_posts.TITLE, vlog_posts.IMAGE,
            vlog_posts.AUTHOR_ID, acc_info.USERNAME
     FROM vlog_posts
     LEFT JOIN acc_info ON acc_info.ID = vlog_posts.AUTHOR_ID
     ORDER BY vlog_posts.PID DESC'
);
$stmt->execute();
$posts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Share Your Blog</h1>

        <?php if ($message !== ''): ?>
            <p><?php echo e($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php" enctype="multipart/form-data" class="post-form">
            <input type="hidden" name="action" value="create">
            <textarea name="content" placeholder="Write your blog post..." required></textarea>
            <input type="file" name="imgcontent" accept="image/jpeg,image/png,image/gif,image/webp">
            <button type="submit" name="post_submit">Post</button>
        </form>

        <div class="post-list">
            <?php if ($posts->num_rows > 0): ?>
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <div class="post-card">
                        <p class="post-author">Posted by <?php echo e($post['USERNAME'] ?? 'Unknown user'); ?></p>
                        <h3><?php echo nl2br(e($post['TITLE'])); ?></h3>
                        <?php if (!empty($post['IMAGE'])): ?>
                            <img src="../imgstorage/<?php echo e($post['IMAGE']); ?>" alt="Post image">
                        <?php endif; ?>
                        <?php if ((int) $post['AUTHOR_ID'] === $userId): ?>
                            <details>
                                <summary>Edit post</summary>
                                <form method="POST" action="index.php" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="post_id" value="<?php echo e($post['PID']); ?>">
                                    <textarea name="content" required><?php echo e($post['TITLE']); ?></textarea>
                                    <input type="file" name="imgcontent" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <button type="submit">Save Changes</button>
                                </form>
                            </details>
                            <form method="POST" action="index.php">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="post_id" value="<?php echo e($post['PID']); ?>">
                                <button type="submit" onclick="return confirm('Delete this post?');">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-posts">No posts to show.</p>
            <?php endif; ?>
        </div>

        <a href="index.php?logout=1">Log out</a>
    </div>
</body>
</html>
