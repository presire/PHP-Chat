<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン・新規登録フォーム</title>
    <link rel="stylesheet" type="text/css" href="css/form.css">
  </head>
  <body>
    <header></header>
    <main>
        <?php
            // 自動ログインチェック
            if (!isset($_SESSION["account"]["id"])) {
                // アプリケーションの初期化
                require_once 'bootstrap.php';

                // DIコンテナのインスタンスを取得
                $container = Container::getInstance();

                $authService = $container->getAuthService();
                
                if (isset($_COOKIE["hashedToken"])) {
                    // $parts       = explode(':', $cookieValue);
                    // $id          = $parts[0];
                    // $hashedToken = $parts[1];
                    $hashedToken = strval($_COOKIE["hashedToken"]);
                    if ($authService->validateRememberMe($hashedToken)) {
                        header('Location: chat.php');
                        exit;
                    }
                }
            }
        ?>

        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">アカウントが正常に作成されました<br/>ログインしてください</p>
        <?php endif; ?>

        <!-- タブ切り替え用ボタン -->
        <!-- タブ切り替え用のラジオボタン -->
        <div class="form-tabs-container">
            <input type="radio" id="login-tab" name="tabs" checked>
            <input type="radio" id="register-tab" name="tabs">

            <!-- タブのラベル -->
            <div class="form-tabs">
                <label for="login-tab" class="tab-label">ログイン</label>
                <label for="register-tab" class="tab-label">新規登録</label>
            </div>

            <!-- フォームコンテナ -->
            <div class="forms-container">
                <!-- ログインフォーム -->
                <form id="loginForm" action="./login_process.php" method="POST" target="_self" class="tab-content">
                    <div class="form-group">
                        <label for="login_id">ログインID (Eメール) : </label>
                        <input type="email" id="login_id" name="form[id]" value="" placeholder="メールアドレスを入力して下さい" required>
                    </div>
                    <div class="form-group">
                        <label for="login_pass">パスワード : </label>
                        <input type="password" id="login_pass" name="form[password]" value="" placeholder="パスワードを入力して下さい" required>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="remember_me" name="form[remember_me]" value="1">
                        <label for="remember_me">ログイン状態を24時間保持する</label>
                    </div>
                    <input type="hidden" name="mode" value="login">
                    <button type="submit" name="login">ログイン</button>
                </form>

                <!-- 新規登録フォーム -->
                <form id="registerForm" action="./register_process.php" method="POST" target="_self" class="tab-content">
                    <div class="form-group">
                        <label for="register_id">メールアドレス : </label>
                        <input type="email" id="register_id" name="form[id]" value="" placeholder="メールアドレスを入力して下さい" required>
                    </div>
                    <div class="form-group">
                        <label for="register_pass">パスワード : </label>
                        <input type="password" id="register_pass" name="form[password]" value="" placeholder="パスワードを入力して下さい" required>
                    </div>
                    <div class="form-group">
                        <label for="register_pass_confirm">パスワード (確認用) : </label>
                        <input type="password" id="register_pass_confirm" name="form[password_confirm]" value="" placeholder="もう一度パスワードを入力して下さい" required>
                    </div>
                    <input type="hidden" name="mode" value="register">
                    <button type="submit" name="register">アカウント作成</button>
                </form>
            </div>
        </div>
    </main>
    <footer></footer>
  </body>
</html>