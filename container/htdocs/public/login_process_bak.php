<?php
    // アプリケーションの初期化
    require_once 'bootstrap.php';

    // セッションの開始
    session_start();

    // 出力バッファリングを開始する前にヘッダ処理を行うため、先頭で処理
    if ($_SERVER['REQUEST_METHOD'] !== "POST") {
        header('Location: login_form.php');
        exit;
    }

    try {
        // POSTデータを取得
        $id         = htmlspecialchars($_POST['form']['id']) ?? '';
        $password   = htmlspecialchars($_POST['form']['password']) ?? '';
        $rememberMe = htmlspecialchars(isset($_POST['form']['remember_me']));

        // $id         = filter_input(INPUT_POST, 'form[id]', FILTER_SANITIZE_EMAIL);
        // $password   = filter_input(INPUT_POST, 'form[password]', FILTER_UNSAFE_RAW);
        // $rememberMe = filter_input(INPUT_POST, 'form[remember_me]', FILTER_VALIDATE_BOOLEAN);

        // 入力値のバリデーション
        if (!$id || !$password) {
            // IDまたはパスワードが入力されていない場合
            throw new RuntimeException('入力データが不正です');
        }

        // DIコンテナのインスタンスを取得
        $container = Container::getInstance();

        // AuthServiceを取得
        $authService = $container->getAuthService();

        // 認証実行
        if ($authService->authenticate($id, $password)) {
        // if ($authService->login($id, $password, $rememberMe)) {
            // 認証成功時

            // セッションIDの再生成 (セッション固定攻撃対策)
            // session_regenerate_id(true);

            // セッションデータの設定
            $_SESSION["user"] = [
                "id"            => $id,
                "login_time"    => time(),
                "last_activity" => time()
            ];

            // ログイン履歴の更新（オプション）
            // $authService->updateLoginHistory($id);

            // chat.phpにリダイレクト
            header('Location: chat.php');
            exit;
        }
        else {
            // 認証失敗時
            throw new RuntimeException('ログインIDまたはパスワードが正しくありません');  // ログインページに戻る
        }
    }
    catch (RuntimeException $e) {
        // エラーメッセージをURLエンコードしてリダイレクト
        $error = urlencode($e->getMessage());
        header("Location: login_form.php?error={$error}");
        exit;
    }
    catch (Exception $e) {
        // 予期しないエラーの場合
        $error = urlencode("システムエラーが発生しました - しばらく時間をおいて再度お試しください");
        header("Location: login_form.php?error={$error}");
        exit;
    }
?>
