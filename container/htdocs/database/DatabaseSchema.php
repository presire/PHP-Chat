<?php
    // テーブル構造の初期化のみを行うクラス
    class DatabaseSchema
    {
        private Database $database;

        // コンストラクタ
        public function __construct(Database $database)
        {
            $this->database = $database;
            $this->initializeDatabase();
        }

        // データベース / テーブルの初期化
        private function initializeDatabase(): void
        {
            $pdo = $this->database->getConnectionRoot();

            // データベースの作成
            $sql = "CREATE DATABASE IF NOT EXISTS chatdb
                    CHARACTER SET utf8mb4
                    COLLATE utf8mb4_unicode_ci";
            $pdo->exec($sql);

            // chatdbの使用を明示的に指定
            $pdo->exec("USE chatdb;");

            // テーブルの作成
            $this->createChatlogTable($pdo);
            $this->createLoginTable($pdo);
        }

        // chatlogテーブルの作成
        private function createChatlogTable(PDO $pdo): void
        {
            $sql = "CREATE TABLE IF NOT EXISTS chatlog (
                        log_id INT AUTO_INCREMENT PRIMARY KEY,
                        name   VARCHAR(255),
                        body   TEXT,
                        ctime  DATETIME,
                        INDEX  idx_chatlog_ctime (ctime)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $pdo->exec($sql);
        }

        // loginテーブルの作成
        private function createLoginTable(PDO $pdo): void
        {
            $sql = "CREATE TABLE IF NOT EXISTS login (
                        login_id varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                        password varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                        remember_token varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        token_expires_at timestamp NULL DEFAULT NULL,
                        last_login_at timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (login_id),
                        KEY idx_login_id (login_id),
                        KEY idx_remember_token (remember_token),
                        KEY idx_token_expires (token_expires_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        }
    }
?>
