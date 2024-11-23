<?php
    // アプリケーションの初期化
    require_once 'bootstrap.php';

    try {
        // フォームデータの取得
        $formData = $_POST["form"] ?? [];

        if (empty($formData["id"]) || empty($formData["password"]) || empty($formData["password_confirm"])) {
            throw new RuntimeException("全ての項目を入力してください");
        }

        // パスワードの確認
        if ($formData["password"] !== $formData["password_confirm"]) {
            throw new RuntimeException("パスワードが一致しません");
        }

        // パスワードの複雑さチェック
        if (strlen($formData["password"]) < 8) {
            throw new RuntimeException("パスワードは8文字以上である必要があります");
        }

        // メールアドレスの形式チェック
        if (!filter_var($formData["id"], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("有効なメールアドレスを入力してください");
        }

        // DIコンテナからAuthServiceを取得
        $container = Container::getInstance();
        $authService = $container->getAuthService();

        // ユーザ登録の実行
        $result = $authService->registerUser($formData["id"], $formData["password"]);

        if ($result) {
            // 登録が成功した場合
            header("Location: login_form.php?success=1");
            exit;
        }
        else {
            // 登録が失敗した場合
            throw new RuntimeException("アカウントの作成に失敗");
        }
    }
    catch (RuntimeException $e) {
        // エラーメッセージをURLエンコードして渡す
        $error = urlencode($e->getMessage());
        header("Location: login_form.php?error={$error}&mode=register");
        exit;
    }
?>
