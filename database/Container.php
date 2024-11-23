<?php
    // DIコンテナ (シングルトンクラス)
    // アプリケーション全体での依存性の注入を管理するクラス
    class Container
    {
        // DIコンテナのシングルトンインスタンス
        private static ?Container $instance = null;

        // 共有インスタンスのキャッシュ
        private array $instances = [];

        // プライベートコンストラクタ
        private function __construct() {}

        // コンテナインスタンスを取得
        // @return Container コンテナインスタンス
        public static function getInstance(): Container
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        public function getDatabase(): Database
        {
            if (!isset($this->instances['database'])) {
                $this->instances['database'] = new Database($this->getDatabaseConfig());
            }

            return $this->instances['database'];
        }

        // DatabaseConfigインスタンスを取得
        // @return DatabaseConfig
        // @throws RuntimeException 設定ファイルの読み込みに失敗した場合
        public function getDatabaseConfig(): DatabaseConfig
        {
            if (!isset($this->instances['databaseConfig'])) {
                $configPath = dirname(__DIR__, 1) . '/config/db.ini';
                $this->instances['databaseConfig'] = new DatabaseConfig($configPath);
            }

            return $this->instances['databaseConfig'];
        }

        // DatabaseSchemaインスタンスを取得
        // @return DatabaseSchema
        public function getDatabaseSchema(): DatabaseSchema
        {
            if (!isset($this->instances['databaseSchema'])) {
                $this->instances['databaseSchema'] = new DatabaseSchema($this->getDatabase());
            }

            return $this->instances['databaseSchema'];
        }

        // ChatRepositoryインスタンスを取得
        // @return ChatRepository
        public function getChatRepository(): ChatRepository
        {
            if (!isset($this->instances['chatRepository'])) {
                $this->instances['chatRepository'] = new ChatRepository($this->getDatabase());
            }

            return $this->instances['chatRepository'];
        }

        // AuthRepositoryインスタンスを取得
        // @return AuthRepository
        public function getAuthRepository(): AuthRepository
        {
            if (!isset($this->instances['authRepository'])) {
                $this->instances['authRepository'] = new AuthRepository($this->getDatabase());
            }

            return $this->instances['authRepository'];
        }

        // AuthServiceインスタンスを取得
        // @return AuthService
        public function getAuthService(): AuthService
        {
            if (!isset($this->instances['authService'])) {
                $this->instances['authService'] = new AuthService($this->getAuthRepository());
            }

            return $this->instances['authService'];
        }
    }
?>
