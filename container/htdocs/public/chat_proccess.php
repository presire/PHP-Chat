<?php
    // アプリケーションの初期化
    require_once 'bootstrap.php';

    // セッションの開始
    session_start();

    // 出力バッファリングを開始する前にヘッダ処理を行うため、先頭で処理
    if ($_SERVER['REQUEST_METHOD'] !== "POST") {
        header('Location: chat.php');
        exit;
    }

    try {
        // DIコンテナのインスタンスを取得
        $container = Container::getInstance();

        // AuthServiceを取得
        $chatRepository = $container->getChatRepository();

        // ログの削除を実行するかどうかを確認
        if (isset($_POST["delete"]) && htmlspecialchars($_POST["delete"]) === "delete") {
            $delete = htmlspecialchars($_POST["delete"]);

            // 全てのログを削除
            if ($chatRepository->deleteAllChatLogs()) {
                // 削除成功時
                // chat.phpにリダイレクト
                header("Location: chat.php");
                exit;
            }
            else {
                // 削除失敗時
                // エラー番号をGETして、chat.phpにリダイレクト
                $display_count = isset($_POST["display_count"]) ? $display_count : 20;
                header("Location: chat.php?error=4&display_count={$display_count}");
                exit;
            }
        }

        // POSTデータを取得
        $name  = htmlspecialchars($_POST["name"]) ?? '';
        $body  = htmlspecialchars($_POST["body"]) ?? '';

        // 入力値のバリデーション
        if (empty($name) || empty($body)) {
            // 名前または本文が入力されていない場合
            header('Location: chat.php?error=1');
            exit;
        }

        // 書き込み
        if ($chatRepository->addChatMessage($name, $body)) {
            // 書き込み成功時
            // chat.phpにリダイレクト
            header('Location: chat.php');
            exit;
        }
        else {
            // 書き込み失敗時
            // エラー番号をGETして、chat.phpにリダイレクト
            header('Location: chat.php?error=2');
            exit;
        }
    }
    catch (RuntimeException $e) {
        // データベース関連のエラーの場合
        // エラー番号とエラーメッセージをGETして、chat.phpにリダイレクト
        $msg = urlencode($e->getMessage());
        header("Location: chat.php?error=3&msg={$msg}");
        exit;
    }
?>
