<?php
    // 認証関連のビジネスロジックを管理するクラス
    class AuthService
    {
        private AuthRepository $authRepository;

        // コンストラクタ
        // @param AuthRepository $authRepository 認証リポジトリインスタンス
        public function __construct(AuthRepository $authRepository)
        {
            $this->authRepository = $authRepository;
        }

        // ユーザ認証を行う
        // @param string $loginId ログインID
        // @param string $password 平文のパスワード
        // @return bool 認証が成功したかどうか
        public function authenticate(string $loginId, string $password): bool
        {
            $user = $this->authRepository->findUserByLoginId($loginId);

            if (!$user) {
                return false;
            }

            return password_verify($password, $user["password"]);
        }

        // 新規ユーザを登録する
        // @param string $loginId ログインID
        // @param string $password 平文のパスワード
        // @return bool 登録が成功したかどうか
        // @throws RuntimeException ユーザが既に存在する場合
        public function registerUser(string $loginId, string $password): bool
        {
            // 既存ユーザの確認
            if ($this->authRepository->findUserByLoginId($loginId)) {
                throw new RuntimeException("このログインIDは既に使用されています");
            }

            // パスワードのハッシュ化
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);

            return $this->authRepository->createUser($loginId, $hashedPassword);
        }

        // ログイン処理
        public function login(string $loginId, string $password, bool $rememberMe = false): bool
        {
            if (!$this->authenticate($loginId, $password)) {
                return false;
            }

            // Remember Me機能が要求された場合
            if ($rememberMe) {
                // セキュアなトークンを生成
                $token       = bin2hex(random_bytes(32));
                $hashedToken = password_hash($token, PASSWORD_BCRYPT);

                // トークンをデータベースに保存
                $this->authRepository->saveRememberMeToken($loginId, $hashedToken);
            }

            return true;
        }

        // Remember Meトークンの取得
        public function getRememberMeToken(string $loginId): ?string
        {
            // データベースからトークンを取得
            return $this->authRepository->getRememberMeToken($loginId);
        }

        // 自動ログインの検証
        public function validateRememberMe(string $hashedToken): bool
        {
            if (!isset($hashedToken)) return false;

            list($loginId, $token) = explode(":", $hashedToken);

            // データベースからトークンを取得
            $storedToken = $this->authRepository->getRememberMeToken($loginId);

            if (!$storedToken || password_verify($token, $storedToken)) {
                return false;
            }

            return true;
        }

        // ログアウト
        public function logout(string $loginId): void
        {
            $this->authRepository->removeRememberMeToken($loginId);
        }
    }
?>
