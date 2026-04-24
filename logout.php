<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

unset($_SESSION['user_id'], $_SESSION['google_oauth_pending_sub'], $_SESSION['oauth_state']);
$_SESSION['toast'] = '로그아웃했습니다.';
header('Location: index.php');
exit;
