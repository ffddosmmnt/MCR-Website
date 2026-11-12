<?php

declare(strict_types=1);

/** Accept only real uploads; decode and rewrite pixels before publishing the file. */
function admin_upload_image(string $field): ?string
{
    $file = $_FILES[$field] ?? null;
    if ($file === null) {
        return null;
    }
    if (!is_array($file) || !is_int($file['error'] ?? null)) {
        throw new InvalidArgumentException('Invalid image upload.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Image upload failed. Choose a JPG, PNG or WebP file up to 2 MB.');
    }
    $temporary = $file['tmp_name'] ?? null;
    if (!is_string($temporary) || !is_uploaded_file($temporary)) {
        throw new InvalidArgumentException('Invalid image upload.');
    }
    $size = filesize($temporary);
    if ($size === false || $size < 1 || $size > 2 * 1024 * 1024) {
        throw new InvalidArgumentException('Image must be no larger than 2 MB.');
    }
    $formats = ['image/jpeg' => ['jpg', IMAGETYPE_JPEG], 'image/png' => ['png', IMAGETYPE_PNG], 'image/webp' => ['webp', IMAGETYPE_WEBP]];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    $dimensions = @getimagesize($temporary);
    if (!isset($formats[$mime]) || $dimensions === false || $dimensions[2] !== $formats[$mime][1]) {
        throw new InvalidArgumentException('Choose a valid JPG, PNG or WebP image.');
    }
    if ($dimensions[0] > 6000 || $dimensions[1] > 6000 || $dimensions[0] * $dimensions[1] > 20000000) {
        throw new InvalidArgumentException('Image must be at most 6000 pixels per side and 20 megapixels.');
    }
    $pixels = @imagecreatefromstring(file_get_contents($temporary));
    if ($pixels === false) {
        throw new InvalidArgumentException('The image cannot be read. Choose another file.');
    }
    $directory = dirname(__DIR__) . '/uploads';
    $path = null;
    $stream = false;
    try {
        if (!is_dir($directory) && !@mkdir($directory, 0755) && !is_dir($directory)) {
            throw new RuntimeException('Image storage is unavailable.');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $formats[$mime][0];
        $path = $directory . '/' . $filename;
        $stream = @fopen($path, 'xb');
        if ($stream === false) {
            throw new RuntimeException('Image storage is unavailable.');
        }
        imagesavealpha($pixels, true);
        $written = match ($mime) {
            'image/jpeg' => imagejpeg($pixels, $stream, 90),
            'image/png' => imagepng($pixels, $stream, 6),
            'image/webp' => imagewebp($pixels, $stream, 90),
        };
        if (!$written || ftell($stream) === 0) {
            throw new RuntimeException('Image could not be saved.');
        }
        return 'dashboard/uploads/' . $filename;
    } catch (Throwable $error) {
        if (is_resource($stream)) {
            fclose($stream);
            $stream = false;
            @unlink($path);
        }
        throw $error;
    } finally {
        if (is_resource($stream)) {
            fclose($stream);
        }
        imagedestroy($pixels);
    }
}

/** Only freshly created, uncommitted uploads may be removed by a failed save. */
function admin_discard_upload(string $path): void
{
    if (preg_match('~\Adashboard/uploads/[a-f0-9]{32}\.(?:jpg|png|webp)\z~', $path)) {
        if (!@unlink(dirname(__DIR__, 2) . '/' . $path)) {
            error_log('[mcr] Uncommitted image cleanup failed.');
        }
    }
}
