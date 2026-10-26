<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    private $db;

    public function __construct($con)
    {
        $this->db = $con;
    }

    public function authenticate($username, $password, $role)
    {
        $stmt = $this->db->prepare(
            'SELECT users.UserID, users.UserName, users.Password, users.RuleID, rules.RuleName
             FROM users
             INNER JOIN rules ON rules.RuleID = users.RuleID
             WHERE users.UserName = :username
             LIMIT 1'
        );
        $stmt->bindValue(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || $this->normalizeRole($user['RuleName']) !== $this->normalizeRole($role)) {
            return false;
        }

        $storedPassword = (string) $user['Password'];
        $passwordInfo = password_get_info($storedPassword);
        $passwordMatches = !empty($passwordInfo['algo'])
            ? password_verify($password, $storedPassword)
            : hash_equals($storedPassword, $password);

        if (!$passwordMatches) {
            return false;
        }

        if (empty($passwordInfo['algo']) || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                throw new RuntimeException('Unable to hash the user password.');
            }

            $update = $this->db->prepare(
                'UPDATE users
                 SET Password = :password
                 WHERE UserID = :user_id AND Password = :current_password'
            );
            $update->bindValue(':password', $hashedPassword);
            $update->bindValue(':user_id', $user['UserID']);
            $update->bindValue(':current_password', $storedPassword);
            $update->execute();
        }

        $this->recordSuccessfulLogin($user['UserID']);

        unset($user['Password']);
        return $user;
    }

    private function recordSuccessfulLogin($userId)
    {
        $this->db->beginTransaction();

        try {
            $userLock = $this->db->prepare(
                'SELECT UserID FROM users WHERE UserID = :user_id FOR UPDATE'
            );
            $userLock->execute([':user_id' => $userId]);

            if ($userLock->fetchColumn() === false) {
                throw new RuntimeException('Unable to record login for an unknown user.');
            }

            $countQuery = $this->db->prepare(
                'SELECT COALESCE(MAX(LoginCount), 0) + 1
                 FROM user_login
                 WHERE UserID = :user_id'
            );
            $countQuery->execute([':user_id' => $userId]);
            $loginCount = (int) $countQuery->fetchColumn();

            $insert = $this->db->prepare(
                'INSERT INTO user_login (UserID, LoginTime, LoginCount)
                 VALUES (:user_id, CURRENT_TIMESTAMP, :login_count)'
            );
            $insert->execute([
                ':user_id' => $userId,
                ':login_count' => $loginCount,
            ]);

            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    private function normalizeRole($role)
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $role));
    }
}
?>
