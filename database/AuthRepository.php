<?php
    // 認証関連のデータアクセスを管理するクラス
class AuthRepository
{
    private Database $database;

    // コンストラクタ
    // @param Database $database データベース接続インスタンス
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    // ログインIDによるユーザ情報の取得
    // @param string $loginId ログインID
    // @return array|null ユーザ情報が見つからない場合はnull
    public function findUserByLoginId(string $loginId): ?array
    {
        try {
            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM login WHERE login_id = :login_id");
            $stmt->bindValue(":login_id", $loginId);
            $stmt->execute();

            $result = $stmt->fetch();
            return $result ?: null;
        }
        catch (PDOException $e) {
            throw new RuntimeException("ユーザ情報の取得に失敗 : " . $e->getMessage(), 0, $e);
        }
    }

    // 新規ユーザの登録
    // @param string $loginId ログインID
    // @param string $hashedPassword ハッシュ化済みのパスワード
    //@return bool 登録が成功したかどうか
    public function createUser(string $loginId, string $hashedPassword): bool
    {
        try {
            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("INSERT INTO login (login_id, password) VALUES (:login_id, :password)");
            $stmt->bindValue(":login_id", $loginId);
            $stmt->bindValue(":password", $hashedPassword);

            return $stmt->execute();
        }
        catch (PDOException $e) {
            throw new RuntimeException("ユーザの登録に失敗 : " . $e->getMessage(), 0, $e);
        }

        return false;
    }

    // Remember Meトークンの保存
    public function saveRememberMeToken(string $loginId, string $hashedToken): bool
    {
        try {
            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("UPDATE login
                                   SET remember_token = :token,
                                   token_expires_at = :expires
                                   WHERE login_id = :login_id");
            $stmt->bindParam(":token", $hashedToken, PDO::PARAM_STR);
            $stmt->bindParam(":expires", date('Y-m-d H:i:s', time() + 86400));
            $stmt->bindParam(":login_id", $loginId, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $e) {
            throw new RuntimeException("トークンの保存に失敗 : " . $e->getMessage());
        }
    }

    // Remember Meトークンの取得
    public function getRememberMeToken(string $loginId): ?string
    {
        try {
            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("SELECT remember_token
                                   FROM login
                                   WHERE login_id = :login_id AND token_expires_at > NOW()");
            $stmt->bindParam(":login_id", $loginId, PDO::PARAM_STR);
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['remember_token'] : null;
        }
        catch (PDOException $e) {
            throw new RuntimeException("トークンの取得に失敗 : " . $e->getMessage());
        }
    }

    // Remember Meトークンの削除
    public function removeRememberMeToken(string $loginId): bool
    {
        try {
            $pdo = $this->database->getConnection();
            $stmt = $pdo->prepare("UPDATE login
                                   SET remember_token = NULL,
                                   token_expires_at = NULL
                                   WHERE login_id = :login_id");
            $stmt->bindParam(":login_id", $loginId, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $e) {
            throw new RuntimeException("トークンの削除に失敗 : " . $e->getMessage());
        }
    }
}
?>
