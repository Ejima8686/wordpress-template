<?php
/*
 * 起動ファイルのパス候補を指定
 * 各アプリケーションのbootstrap.phpファイルのパスを指定
 */
$bootstrap_candidates = [
	/* 絶対パスでの探索（環境に合わせて設定） */
	"/home/account_name/app/lp/bootstrap.php", #さくら、XServerの場合
	"/var/www/app/lp/bootstrap.php", #Docker開発環境の場合
	/* 相対パスでの探索（フォールバック） */
	__DIR__ . "/../../../../app/lp/bootstrap.php",
	__DIR__ . "/../../../app/lp/bootstrap.php",
	__DIR__ . "/../../app/lp/bootstrap.php",
	__DIR__ . "/../app/lp/bootstrap.php",
];

/*
 * 起動ファイルを探索
 */
foreach ($bootstrap_candidates as $bootstrap) {
	$found_bootstrap = "";
	if (file_exists($bootstrap)) {
		$found_bootstrap = $bootstrap;
		break;
	}
}

/*
 * 起動ファイルを読み込む
 */
if ($found_bootstrap) {
	try {
		require_once $found_bootstrap;
	} catch (Exception $e) {
		error_log("Bootstrap file error: " . $e->getMessage());
		exit(1);
	}
} else {
	error_log("Bootstrap file not found");
	exit(1);
}
