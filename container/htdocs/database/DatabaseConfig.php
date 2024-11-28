<?php

    // データベース設定を管理するクラス
    class DatabaseConfig
    {
        private array $config;

        // コンストラクタ
        // @param string $configPath 設定ファイルのパス
        // @throws RuntimeException 設定ファイルが読み込めない場合
        public function __construct(string $configPath)
        {
            if (!file_exists($configPath)) {
                throw new RuntimeException("INIファイルが存在しない: {$configPath}");
            }

            $ini = parse_ini_file($configPath, true);
            if ($ini === false || !isset($ini['MYSQL'])) {
                throw new RuntimeException("INIファイルの読み込みに失敗");
            }

            $this->config = $ini['MYSQL'];
        }

        // データベース接続情報を取得
        public function getConnectionInfo(): array
        {
            return ['host'     => $this->config['DB_HOST'] ?? 'localhost',
                    'dbname'   => $this->config['DB_NAME'] ?? 'chatdb',
                    'username' => $this->config['DB_USER'] ?? 'root',
                    'password' => $this->config['DB_PASS'] ?? 'root',
                    'port'     => $this->config['DB_PORT'] ?? '3306',
                    'charset'  => 'utf8mb4'
            ];
        }
    }
?>
