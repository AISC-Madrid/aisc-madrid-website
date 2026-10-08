<?php
// member_photo.php — member photo helpers shared by add_team_member.php and update_team_member.php.
// Photos are compressed to WebP and stored in the S3 media bucket under "members/{id}/".

require_once __DIR__ . '/../../events/upload_image.php';

/**
 * Upload the photo sent in $_FILES[$fieldName] for member $memberId.
 *
 * @return array|null  null if no file was sent, ['path' => bucket key] or ['error' => '...']
 */
function upload_member_photo(string $fieldName, int $memberId): ?array
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }
    return handleImageUpload($fieldName, "members/$memberId");
}

/**
 * Delete a member photo from the bucket if it lives there.
 * Accepts both bucket keys ("members/12/img_x.webp") and full bucket URLs
 * (legacy rows store "https://s3.aiscmadrid.com/aisc-public/members/x.webp").
 * Anything else (Cloudinary, local paths) is left untouched.
 */
function delete_member_photo(?string $imagePath): void
{
    if ($imagePath === null || $imagePath === '') {
        return;
    }

    $key = $imagePath;
    $base = media_url('');
    if (strpos($imagePath, $base) === 0) {
        $key = rawurldecode(substr($imagePath, strlen($base)));
    }

    if (strpos($key, 'members/') !== 0) {
        return;
    }

    $result = s3_delete_object($key);
    if (isset($result['error'])) {
        error_log("Could not delete old member photo $key: " . $result['error']);
    }
}
