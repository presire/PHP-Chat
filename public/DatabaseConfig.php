<?php
    class DatabaseSetup
    {
        private $pdo;
        private string $host;
        private string $dbname;
        private string $username;
        private string $password;
        private string $errorMessage;

        public function __construct()
        {
        }

        public function __destruct()
        {
        }

        public function getConnection(string $fileName) : bool
        {
            if (!file_exists($fileName)) return false;

            // データベース設定の読み込み
            $iniFile = dirname(__FILE__) . "etc/" . "db.ini";
            $db_ini = parse_ini_file($iniFile, true);
            if ($db_ini === false) {
                return false;
            }

            // [MYSQL]セクションの存在を確認
            if (!isset($db_ini["MYSQL"])) {
                $this->errorMessage = "指定されたセクション [MYSQL] が存在しない";
            }

            $section = $db_ini["MYSQL"];

            // MySQL接続情報
            $this->host     = $section['DB_HOST'] ?? 'localhost';  // MySQLのホスト名
            $this->dbname   = $section['DB_NAME'] ?? '';           // MySQLのデータベース名
            $this->username = $section['DB_USER'] ?? 'root';       // MySQLのデータベースユーザ名
            $this->password = $section['DB_PASS'] ?? 'root';       // MySQLのデータベースユーザのパスワード
            $charset        = 'utf8mb4';

            // DSN (Data Source Name) の構築
            $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=$charset";

            $options = [
                // エラー発生時に例外をスロー
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                // フェッチモードをデフォルトで連想配列に設定
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // プリペアドステートメントを有効化
                PDO::ATTR_EMULATE_PREPARES => false,

                // 持続的な接続を無効化
                PDO::ATTR_PERSISTENT => false,

                // カラム名を小文字に変換
                PDO::ATTR_CASE => PDO::CASE_LOWER
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);

            return true;
        }

        private function createDatabase()
        {
            try {
                if (!$this->pdo) {
                    throw new Exception("データベースのインスタンスが存在しない");
                }

                // データベースの作成（もし存在しない場合）
                // デフォルトのストレージエンジンの指定
                $sql = "CREATE DATABASE IF NOT EXISTS chatdb
                        CHARACTER SET utf8mb4
                        COLLATE utf8mb4_unicode_ci;
                        DEFAULT STORAGE ENGINE = InnoDB;";

                $this->pdo->exec($sql);
            }
            catch (PDOException $e) {
                $this->errorMessage = "chatデータベース作成エラー : " . $e->getMessage();
                return false;
            }
            catch (Exception $e) {
                $this->errorMessage = "致命的なエラー : " . $e->getMessage();
                return false;
            }

            return true;
        }

        public function createTables() : bool
        {
            try {
                // データベースが存在しない場合は作成
                if (!$this->createDatabase()) {
                    throw new Exception("chatデータベースの作成に失敗");
                }

                // chatlogテーブルの作成
                // DATETIME型の使用 : タイムゾーンの変換が不要, 広い日付範囲をサポートしているため
                // ソート処理の効率化のため、ctimeカラムにインデックスを追加
                $sql = "CREATE TABLE IF NOT EXISTS chatlog (
                            log_id INT AUTO_INCREMENT PRIMARY KEY,
                            name   VARCHAR(255),
                            body   TEXT,
                            ctime  DATETIME,
                            INDEX  idx_chatlog_ctime (ctime)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

                $this->pdo->exec($sql);
            }
            catch (PDOException $e) {
                $this->errorMessage = "chatテーブル作成エラー : " . $e->getMessage();
                return false;
            }
            catch (Exception $e) {
                $this->errorMessage = "致命的なエラー : " . $e->getMessage();
                return false;
            }

            return true;
        }

        public function closeConnection() {
            $this->pdo = null;
        }

        // ログイン処理
        public function login($email, $password)
        {
            try {
                // MySQLに接続
                $db   = getConnection();

                // データベースが存在しない場合は作成
                if (!$this->createDatabase()) {
                    throw new Exception("chatデータベースの作成に失敗");
                }

                $sql  = "SELECT * FROM accounts WHERE email = :email";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                // 結果を取得
                // PDO::FETCH_ASSOCは、データベースの結果を連想配列として取得するオプション
                // FETCH_ASSOCを使用すると、カラム名をキーとした連想配列のみが返る
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // データベースに保存されているbcryptハッシュとパスワードを照合
                // password_verify関数は、パスワードの照合用
                if (count($rows) === 1 &&
                    password_verify($password, $rows[0]['password'])) {
                    return $rows[0];
                }
            }
            catch (PDOException $e) {
                $this->errorMessage = "ログイン処理でエラーが発生 : " . $e->getMessage();
            }
            catch (Exception $e) {
                $this->errorMessage = "致命的なエラー : " . $e->getMessage();
            }

            return null;
        }

        // ログイン認証
        public function authCheck($email, $password)
        {
            return login($email, $password);
        }

        // bcryptでハッシュ化 (暗号化用)
        private function hashPassword($var)
        {
            // 第1引数: ハッシュ化するパスワード文字列
            // 第2引数: ハッシュアルゴリズム (PASSWORD_BCRYPT, PASSWORD_ARGON2I等)
            // 第3引数: オプション配列

            // costパラメータの説明:
            // ['cost' => 12]: ハッシュ化の計算回数を2の12乗(4,096)回に設定
            // 値域: 4-31 (デフォルトは10)
            // 大きいほど安全だが、処理時間も増加
            // 12は一般的な推奨値 (セキュリティと性能のバランス)
            return password_hash($var, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        public function getErrorMessage() : string
        {
            return $this->errorMessage;
        }
    }
?>
