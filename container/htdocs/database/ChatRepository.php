<?php
    // チャットログのデータアクセスを管理するクラス
    class ChatRepository
    {
        private Database $database;

        // コンストラクタ
        // @param Database $database データベース接続インスタンス
        public function __construct(Database $database)
        {
            $this->database = $database;
        }

        // 最新のチャットログを取得
        // @param int $limit 取得する件数
        // @return array チャットログの配列
        // @throws PDOException データベースエラーが発生した場合
        public function getRecentChatLogs(int $limit = 50, int $offset = 0): array
        {
            try {
                $pdo = $this->database->getConnection();
                $stmt = $pdo->prepare("SELECT * FROM chatlog
                                       ORDER BY ctime DESC
                                       LIMIT :limit
                                       OFFSET :offset;");
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll();
            }
            catch (PDOException $e) {
                throw new RuntimeException("チャットログの取得に失敗 : " . $e->getMessage(), 0, $e);
            }
        }

        // 新しいチャットメッセージを追加
        // @param string $name ユーザー名
        // @param string $body メッセージ本文
        // @return bool 追加が成功したかどうか
        // @throws PDOException データベースエラーが発生した場合
        public function addChatMessage(string $name, string $body): bool
        {
            try {
                $pdo = $this->database->getConnection();
                $stmt = $pdo->prepare("INSERT INTO chatlog (name, body, ctime) VALUES (:name, :body, NOW())");
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':body', $body, PDO::PARAM_STR);

                return $stmt->execute();
            }
            catch (PDOException $e) {
                throw new RuntimeException("メッセージの追加に失敗 : " . $e->getMessage(), 0, $e);
            }

            return false;
        }

        // 指定した日時以降のチャットログを取得
        // @param DateTime $since この日時以降のログを取得
        // @return array チャットログの配列
        // @throws PDOException データベースエラーが発生した場合
        public function getChatLogsSince(DateTime $since): array
        {
            try {
                $pdo = $this->database->getConnection();
                $stmt = $pdo->prepare("SELECT * FROM chatlog
                                       WHERE ctime > :since
                                       ORDER BY ctime ASC;");
                $stmt->bindValue(':since', $since->format('Y-m-d H:i:s'));
                $stmt->execute();

                return $stmt->fetchAll();
            }
            catch (PDOException $e) {
                throw new RuntimeException("チャットログの取得に失敗 : " . $e->getMessage(), 0, $e);
            }
        }

        // 指定した期間のチャットログを取得
        // @param DateTime $start 開始日時
        // @param DateTime $end 終了日時
        // @return array チャットログの配列
        // @throws PDOException データベースエラーが発生した場合
        public function getChatLogsByDateRange(DateTime $start, DateTime $end): array
        {
            try {
                $pdo = $this->database->getConnection();
                $stmt = $pdo->prepare("SELECT * FROM chatlog
                                       WHERE ctime BETWEEN :start AND :end
                                       ORDER BY ctime ASC;");

                $stmt->bindValue(':start', $start->format('Y-m-d H:i:s'));
                $stmt->bindValue(':end', $end->format('Y-m-d H:i:s'));
                $stmt->execute();

                return $stmt->fetchAll();
            }
            catch (PDOException $e) {
                throw new RuntimeException("チャットログの取得に失敗 : " . $e->getMessage(), 0, $e);
            }
        }

        // チャットログを全削除
        // @return bool 削除が成功したかどうか
        // @throws PDOException データベースエラーが発生した場合
        public function deleteAllChatLogs(): bool
        {
            try {
                $pdo  = $this->database->getConnection();
                $stmt = $pdo->prepare("TRUNCATE TABLE chatlog;");

                return $stmt->execute();
            }
            catch (PDOException $e) {
                throw new RuntimeException("ログの削除に失敗 : " . $e->getMessage());
            }

            return false;
        }
    }
?>
