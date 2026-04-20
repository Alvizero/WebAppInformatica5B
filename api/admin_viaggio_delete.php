
<?php
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();
$id = (int)($_POST['viaggio_id'] ?? 0);
if ($id > 0) {
    getPDO()->prepare("DELETE FROM viaggi WHERE id=:id")->execute(['id' => $id]);
}
header('Location: ./../pages/admin/admin.php');
exit;
