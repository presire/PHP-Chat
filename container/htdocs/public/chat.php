<?php
    // セッション開始
    session_start();

    // ログインチェック
    if (!isset($_SESSION["account"]["login"]) || $_SESSION["account"]["login"] !== true) {
        // 未ログインの場合、ログインページにリダイレクト
        header('Location: login_form.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mochiuのチャット</title>
        <link rel="stylesheet" type="text/css" href="css/form.css">
        <link rel="stylesheet" type="text/css" href="css/chat.css">
    </head>
    <body>
        <header></header>
        <main>
            <p>ようこそ、<?php echo htmlspecialchars($_SESSION["account"]["id"]); ?>さん</p>

            <!-- ログアウトリンク -->
            <p><a href="logout.php">ログアウト</a></p>

            <!-- チャットのコンテンツ -->
            <?php
                require_once 'bootstrap.php';

                // 1ページ内に表示するチャットログの数 (デフォルト)
                if (isset($_GET["display_count"])) {
                    $display_count = intval(htmlspecialchars($_GET["display_count"]));
                }
                else {
                    $display_count = 20;
                }

                $page_max = $display_count;
                $limit    = $page_max + 1;  // ページャ判定のため1件多めに取得する

                // オフセット
                $offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;

                // データベースへの接続
                try {
                    // DIコンテナのインスタンスを取得
                    $container = Container::getInstance();

                    // ChatRepositoryを取得
                    $chatRepository = $container->getChatRepository();

                    // チャットログの取得
                    $messages   = $chatRepository->getRecentChatLogs($limit, $offset);

                    require_once "chat_template.php";
                }
                catch (PDOException $e) {
                    echo "データベースエラー : ".$e->getMessage();
                    exit;
                }
                catch (Exception $e) {
                    echo "致命的なエラーが発生 : " . $e->getMessage();
                    exit;
                }

                // 書き込み後の確認
                if (isset($_GET["error"])) {
                    $errno = htmlspecialchars($_GET["error"]);
                    if ($errno === 1) {
                        echo "<p>名前または本文が入力されていません</p><br/>";
                    }
                    else if ($errno === 2) {
                        echo "<p>書き込みに失敗しました</p><br/>";
                    }
                    else if ($errno === 3) {
                        if (isset($_GET["msg"])) {
                            $msg = htmlspecialchars($_GET["msg"]);
                            $decodeMsg = urldecode($msg);
                            echo "<p>エラー : {$decodeMsg}</p><br/>";
                        }
                    }
                    else if ($errno === 4) {
                        echo "<p>ログの削除に失敗しました</p><br/>";
                    }
                }
            ?>
        </main>
        <footer></footer>
    </body>
</html>
