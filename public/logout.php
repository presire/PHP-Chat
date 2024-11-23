<?php
    // ログアウト画面
    session_start();
    
    if (isset($_SESSION["account"]["id"])) {
        $id = htmlspecialchars($_SESSION["account"]["id"]);
        // アプリケーションの初期化
        require_once 'bootstrap.php';

        // DIコンテナのインスタンスを取得
        $container = Container::getInstance();

        // AuthServiceを取得
        $authService = $container->getAuthService();
        $authService->logout($id);
    }

    // セッションを破棄
    $_SESSION = array();
    session_destroy();

    setcookie(
        "hashedToken",
        "",
        [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Strict'
        ]);
    
    // ログインページにリダイレクト
    header('Location: login_form.php');
    exit;
?>
