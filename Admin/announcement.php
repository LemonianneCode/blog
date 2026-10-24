<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/no_cache.php';
require_once __DIR__ . '/../includes/db_conn.php';

if (empty($_SESSION['announcement_csrf_token'])) {
    $_SESSION['announcement_csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = 'error';
$maxImageSize = 10 * 1024 * 1024;

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function iniBytes(string $value): int
{
    $value = trim($value);
    $unit = strtolower(substr($value, -1));
    $bytes = (float) $value;

    switch ($unit) {
        case 'g':
            $bytes *= 1024 ** 3;
            break;
        case 'm':
            $bytes *= 1024 ** 2;
            break;
        case 'k':
            $bytes *= 1024;
            break;
    }

    return (int) $bytes;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0 &&
    iniBytes(ini_get('post_max_size')) > 0 &&
    (int) $_SERVER['CONTENT_LENGTH'] > iniBytes(ini_get('post_max_size'))
) {
    $message = 'The image or announcement exceeds PHP\'s request size limit.';
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $message === '' &&
    ($_POST['action'] ?? '') === 'publish'
) {
    $csrfToken = $_POST['csrf_token'] ?? null;
    $content = $_POST['content'] ?? '';

    if (
        !is_string($csrfToken) ||
        !hash_equals($_SESSION['announcement_csrf_token'], $csrfToken)
    ) {
        $message = 'Your session has expired. Please reload the page and try again.';
    } elseif (!is_string($content) || trim($content) === '') {
        $message = 'Write an announcement before publishing.';
    } elseif (
        preg_match_all('/./us', trim($content), $matches) === false ||
        count($matches[0]) > 5000
    ) {
        $message = 'Announcements must be 5,000 characters or fewer.';
    } else {
        $content = trim($content);
        $imageName = null;
        $upload = $_FILES['image'] ?? null;

        if (is_array($upload) && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($upload['error'] !== UPLOAD_ERR_OK) {
                $message = $upload['error'] === UPLOAD_ERR_INI_SIZE
                    || $upload['error'] === UPLOAD_ERR_FORM_SIZE
                    ? 'The image exceeds the upload size limit.'
                    : 'The image could not be uploaded.';
            } elseif ($upload['size'] > $maxImageSize) {
                $message = 'Images must be 10 MB or smaller.';
            } else {
                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    'image/bmp' => 'bmp',
                    'image/x-ms-bmp' => 'bmp',
                    'image/avif' => 'avif',
                ];
                $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $fileInfo->file($upload['tmp_name']);

                if (!is_string($mimeType) || !isset($extensions[$mimeType])) {
                    $message = 'Upload a JPG, PNG, GIF, WebP, BMP, or AVIF image.';
                } else {
                    $imageName = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
                    $imagePath = __DIR__ . '/../imgstorage/' . $imageName;
                    if (!move_uploaded_file($upload['tmp_name'], $imagePath)) {
                        $message = 'The image could not be saved.';
                    }
                }
            }
        }

        if ($message === '') {
            $stmt = $dbconn->prepare(
                'INSERT INTO announcement_posts (CONTENT, IMAGE) VALUES (?, ?)'
            );
            if (!$stmt) {
                http_response_code(500);
                exit('Unable to publish the announcement.');
            }

            $stmt->bind_param('ss', $content, $imageName);
            if ($stmt->execute()) {
                $message = 'Announcement published.';
                $messageType = 'success';
            } else {
                if ($imageName !== null) {
                    $imagePath = __DIR__ . '/../imgstorage/' . $imageName;
                    if (is_file($imagePath)) {
                        unlink($imagePath);
                    }
                }
                http_response_code(500);
                $stmt->close();
                exit('Unable to publish the announcement.');
            }
            $stmt->close();
        }
    }
}

$stmt = $dbconn->prepare(
    'SELECT ID, CONTENT, IMAGE, CREATED_AT
     FROM announcement_posts
     ORDER BY CREATED_AT DESC, ID DESC'
);
if (!$stmt || !$stmt->execute()) {
    http_response_code(500);
    exit('Unable to load announcements.');
}
$announcements = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css">
  <title>Announcements | Admin</title>
</head>
<body>
  <main class="admin-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>Announcements</h1>
        <p class="page-description">Publish updates for everyone using the blog.</p>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <p class="notice <?php echo $messageType === 'success' ? 'notice-success' : ''; ?>" role="<?php echo $messageType === 'success' ? 'status' : 'alert'; ?>">
        <?php echo e($message); ?>
      </p>
    <?php endif; ?>

    <section class="form-card" aria-labelledby="announcement-form-heading">
      <h2 id="announcement-form-heading">Write an announcement</h2>
      <p class="section-description">Add a message and an optional image. The announcement will appear to all signed-in users.</p>
      <form class="announcement-form" method="POST" action="announcement.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="publish">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['announcement_csrf_token']); ?>">
        <label for="announcement-content">Announcement</label>
        <textarea id="announcement-content" name="content" maxlength="5000" required></textarea>
        <label for="announcement-image">Image (optional)</label>
        <input type="file" id="announcement-image" name="image" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/avif">
        <small>JPG, PNG, GIF, WebP, BMP, or AVIF; maximum 10 MB.</small>
        <button type="submit">Publish announcement</button>
      </form>
    </section>

    <section class="announcement-list" aria-labelledby="published-heading">
      <h2 id="published-heading">Published announcements</h2>
      <?php if ($announcements->num_rows > 0): ?>
        <?php while ($announcement = $announcements->fetch_assoc()): ?>
          <article class="announcement-card">
            <time datetime="<?php echo e(date('c', strtotime($announcement['CREATED_AT']))); ?>">
              <?php echo e(date('F j, Y \a\t g:i a', strtotime($announcement['CREATED_AT']))); ?>
            </time>
            <p><?php echo nl2br(e($announcement['CONTENT'])); ?></p>
            <?php if (!empty($announcement['IMAGE'])): ?>
              <img src="../imgstorage/<?php echo e(basename($announcement['IMAGE'])); ?>" alt="Announcement image">
            <?php endif; ?>
          </article>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="page-description">No announcements have been published yet.</p>
      <?php endif; ?>
    </section>
    <nav class="admin-actions" aria-label="Admin navigation">
      <a class="button button-quiet" href="index.php">Back to accounts</a>
      <a class="button button-quiet" href="../index.php?logout=1">Log out</a>
    </nav>
  </main>
  <script src="../includes/session-page.js"></script>
</body>
</html>
<?php $stmt->close(); ?>
