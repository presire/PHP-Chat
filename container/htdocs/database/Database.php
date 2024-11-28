<?php
    // PDO接続の管理のみを行うクラス
    class Database
    {
        // データベースインスタンス
        private ?PDO $connection = null;

        // データベース設定のインスタンス
        private DatabaseConfig $config;

        // コンストラクタ
        public function __construct(DatabaseConfig $config)
        {
            $this->config = $config;
        }

        // デストラクタ
        public function __destruct()
        {
            if ($this->connection != null) {
                $this->disconnect();
            }
        }

        public function getConnection(): PDO
        {
            if ($this->connection === null) {
                $this->connect();
            }
            return $this->connection;
        }

        public function getConnectionRoot(): PDO
        {
            if ($this->connection === null) {
                $this->connectToRoot();
            }
            return $this->connection;
        }

        // データベースの接続
        private function connect(): void
        {
            try {
                $info = $this->config->getConnectionInfo();
                $dsn = "mysql:host={$info['host']};port={$info['port']};dbname={$info['dbname']};charset={$info['charset']}";

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => false,
                    PDO::ATTR_CASE               => PDO::CASE_LOWER
                ];

                $this->connection = new PDO($dsn, $info['username'], $info['password'], $options);
            }
            catch (PDOException $e) {
                throw new RuntimeException("データベース接続エラー: " . $e->getMessage());
            }
        }

        // データベースの接続 (データベースの指定なし)
        private function connectToRoot(): void
        {
            try {
                $info = $this->config->getConnectionInfo();
                $dsn = "mysql:host={$info['host']};port={$info['port']};charset={$info['charset']}";

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => false,
                    PDO::ATTR_CASE               => PDO::CASE_LOWER
                ];

                $this->connection = new PDO($dsn, $info['username'], $info['password'], $options);
            }
            catch (PDOException $e) {
                throw new RuntimeException("データベース接続エラー: " . $e->getMessage());
            }
        }

        // データベースの切断
        private function disconnect(): void
        {
            $this->connection = null;
        }
    }
?>
