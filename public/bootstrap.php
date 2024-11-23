<?php
    // オートローダーの設定
    spl_autoload_register(function ($class) {
        // プロジェクトのルートディレクトリを取得
        $root_dir = dirname(__DIR__, 1);
        
        // 名前空間の区切り文字をディレクトリ区切り文字に変換
        $class_file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        
        // 検索対象のディレクトリを配列で定義
        $directories = [
            $root_dir . '/database/'
        ];
        
        // 各ディレクトリでファイルを検索
        foreach ($directories as $directory) {
            // 完全なファイルパスを構築
            $file = $directory . $class_file . '.php';
            $file_without_namespace = $directory . basename($class_file) . '.php';
            
            // 名前空間付きのパスで検索
            if (file_exists($file)) {
                require_once $file;
                return;
            }
            
            // 名前空間なしのパスで検索
            if (file_exists($file_without_namespace)) {
                require_once $file_without_namespace;
                return;
            }
        }
    });

    // エラー報告の設定
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    // タイムゾーンの設定
    date_default_timezone_set('Asia/Tokyo');

    // DIコンテナのインスタンスを取得
    require_once dirname(__DIR__, 1) . '/database/Container.php';
    $container = Container::getInstance();

    // データベーススキーマの初期化
    // try {
    //     $schema = $container->getDatabaseSchema();
    // }
    // catch (Exception $e) {
    //     error_log('データベース初期化エラー : ' . $e->getMessage());
    //     throw $e;
    // }
?>
