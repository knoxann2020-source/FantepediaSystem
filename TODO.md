# Task: Fix fatal mysqli_sql_exception - Table 'fs.thumbnails' doesn't exist

## Steps
- [x] Remove the legacy `thumbnails` table query block from `admin/delete-user.php`
- [x] Update the preceding comment to reflect that only post thumbnail/post_image are cleaned
- [x] Verify PHP syntax (no errors detected)
- [ ] Test deleting a user from the admin panel to confirm no fatal error

## Status
- [x] Analysis complete: `thumbnails` table does not exist in schema; `posts` table already has `thumbnail` & `post_image` columns handled above.

