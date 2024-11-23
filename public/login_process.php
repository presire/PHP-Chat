<?php
    // アプリケーションの初期化
    require_once "bootstrap.php";

    // セッションの開始
    session_start();

    // 出力バッファリングを開始する前にヘッダ処理を行うため、先頭で処理
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: login_form.php");
        exit;
    }

    try {
        // DIコンテナのインスタンスを取得
        $container = Container::getInstance();

        // AuthServiceを取得
        $authService = $container->getAuthService();

        // POSTデータを取得
        $id         = htmlspecialchars($_POST["form"]["id"]) ?? "";
        $password   = htmlspecialchars($_POST["form"]["password"]) ?? "";
        $rememberMe = htmlspecialchars(isset($_POST["form"]["remember_me"]));

        // 入力値のバリデーション
        if (empty($id) || empty($password)) {
            // IDまたはパスワードが入力されていない場合
            throw new RuntimeException("入力データが不正です");
        }

        // 認証実行
        // if ($authService->authenticate($id, $password)) {
        if ($authService->login($id, $password, $rememberMe)) {
                // 認証成功時
                // セッションに認証情報を保存
                $_SESSION["account"] = array(
                    "id"            => $id,
                    "login_time"    => time(),
                    "last_activity" => time(),
                    "login"         => true
                );

                $hashedToken = $authService->getRememberMeToken($id);
                if (isset($hashedToken)) {
                    // クッキーを設定（24時間）
                    setcookie(
                        "hashedToken",
                        $id . ':' . $hashedToken,
                        [
                            'expires'  => time() + 86400,
                            'path'     => '/',
                            'httponly' => false,
                            'secure'   => false,
                            'samesite' => 'Strict'
                        ]
                    );
                }

                // chat.phpにリダイレクト
                header("Location: chat.php");
                exit;
        }
        else {
            // 認証失敗時
            throw new RuntimeException("ログインIDまたはパスワードが正しくありません");
        }
    }
    catch (RuntimeException $e) {
        // エラーメッセージをURLエンコードしてリダイレクト
        $error = urlencode($e->getMessage());
        header("Location: login_form.php?error={$error}");
        exit;
    }
    catch (Exception $e) {
        // 予期しないエラーの場合は一般的なエラーメッセージを表示
        $error = urlencode("システムエラーが発生 - しばらく時間をおいて再度お試しください");
        header("Location: login_form.php?error={$error}");
        exit;
    }
?>
