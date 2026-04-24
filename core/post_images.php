<?php
declare(strict_types=1);

/**
 * @return array<int, array<string, mixed>>
 */
function post_images_for_post(int $postId): array
{
    $st = db()->prepare(
        'SELECT id, post_id, path, sort_order, created_at FROM post_images WHERE post_id = ? ORDER BY sort_order ASC, id ASC'
    );
    $st->execute([$postId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
 */
function post_images_try_save_upload(int $postId, array $file, int $sortOrder): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return null;
    }
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 2_500_000) {
        return null;
    }
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
    } else {
        $mime = (string) ($file['type'] ?? '');
    }
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $ext = $extMap[$mime] ?? '';
    if ($ext === '') {
        return null;
    }
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return null;
    }
    $name = $postId . '_' . $sortOrder . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destFs = $dir . '/' . $name;
    if (!move_uploaded_file($tmp, $destFs)) {
        return null;
    }
    $rel = 'uploads/' . $name;
    $st = db()->prepare('INSERT INTO post_images (post_id, path, sort_order, created_at) VALUES (?,?,?,?)');
    $st->execute([$postId, $rel, $sortOrder, time()]);

    return $rel;
}

/**
 * @param mixed $filesField $_FILES['images'] (single or multi)
 */
function post_images_save_from_form(int $postId, $filesField): void
{
    if (!is_array($filesField)) {
        return;
    }
    if (!isset($filesField['name']) || !is_array($filesField['name'])) {
        /** @var array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $filesField */
        if (($filesField['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            post_images_try_save_upload($postId, $filesField, 0);
        }
        return;
    }
    $max = min(5, count($filesField['name']));
    $sort = 0;
    for ($i = 0; $i < $max; $i++) {
        $file = [
            'name' => $filesField['name'][$i] ?? '',
            'type' => $filesField['type'][$i] ?? '',
            'tmp_name' => $filesField['tmp_name'][$i] ?? '',
            'error' => (int) ($filesField['error'][$i] ?? UPLOAD_ERR_NO_FILE),
            'size' => (int) ($filesField['size'][$i] ?? 0),
        ];
        if (post_images_try_save_upload($postId, $file, $sort) !== null) {
            $sort++;
        }
    }
}
