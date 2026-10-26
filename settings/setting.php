<?php
$page_css = '/Jewelry-app/settings/setting.css';
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();
require_once __DIR__ . '/../config/database.php';

$errorMessage = null;
$currentUserId = (string) $_SESSION['user']['UserID'];
$currentRoleStatement = $con->prepare(
    'SELECT rules.RuleName
     FROM users
     INNER JOIN rules ON rules.RuleID = users.RuleID
     WHERE users.UserID = :user_id
     LIMIT 1'
);
$currentRoleStatement->execute([':user_id' => $currentUserId]);
$currentRole = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $currentRoleStatement->fetchColumn()));
$canManageUsers = $currentRole === 'admin';
$csrfToken = $_SESSION['users_csrf_token'] ??= bin2hex(random_bytes(32));
$editUser = null;
$editUserId = '';
$rolesStatement = $con->query('SELECT RuleID, RuleName FROM rules ORDER BY RuleName');
$roles = $rolesStatement->fetchAll(PDO::FETCH_ASSOC);

$userAction = isset($_POST['user_action']) && is_string($_POST['user_action'])
    ? $_POST['user_action']
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($userAction, ['edit', 'delete'], true)) {
    $submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
        ? $_POST['csrf_token']
        : '';
    $targetUserId = isset($_POST['user_id']) && is_string($_POST['user_id'])
        ? trim($_POST['user_id'])
        : '';

    if (!$canManageUsers) {
        $errorMessage = 'Only administrators can edit or delete user accounts.';
    } elseif (!hash_equals($csrfToken, $submittedToken)) {
        $errorMessage = 'Your session expired. Please try again.';
    } elseif ($targetUserId === '') {
        $errorMessage = 'Select a valid user account.';
    } elseif ($userAction === 'edit') {
        $editUserId = $targetUserId;
        $username = isset($_POST['username']) && is_string($_POST['username'])
            ? trim($_POST['username'])
            : '';
        $phone = isset($_POST['phone']) && is_string($_POST['phone'])
            ? trim($_POST['phone'])
            : '';
        $birthDate = isset($_POST['birth_date']) && is_string($_POST['birth_date'])
            ? trim($_POST['birth_date'])
            : '';
        $ruleId = isset($_POST['rule_id']) && is_string($_POST['rule_id'])
            ? $_POST['rule_id']
            : '';
        $selectedRole = null;

        foreach ($roles as $role) {
            if ((string) $role['RuleID'] === $ruleId) {
                $selectedRole = $role;
                break;
            }
        }

        if ($username === '' || strlen($username) > 50) {
            $errorMessage = 'Enter a username of 1 to 50 characters.';
        } elseif (strlen($phone) > 20) {
            $errorMessage = 'Phone number must be no more than 20 characters.';
        } elseif ($birthDate !== '' && (
            ($date = DateTime::createFromFormat('!Y-m-d', $birthDate)) === false
            || $date->format('Y-m-d') !== $birthDate
        )) {
            $errorMessage = 'Enter a valid birth date.';
        } elseif ($selectedRole === null) {
            $errorMessage = 'Select a valid role.';
        } elseif (
            $targetUserId === $currentUserId
            && $currentRole === 'admin'
            && strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $selectedRole['RuleName'])) !== 'admin'
        ) {
            $errorMessage = 'You cannot remove your own administrator access.';
        } else {
            $usernameExists = $con->prepare(
                'SELECT 1 FROM users WHERE UserName = :username AND UserID <> :user_id LIMIT 1'
            );
            $usernameExists->execute([
                ':username' => $username,
                ':user_id' => $targetUserId,
            ]);

            if ($usernameExists->fetchColumn() !== false) {
                $errorMessage = 'That username is already in use.';
            } else {
                $updateUser = $con->prepare(
                    'UPDATE users
                     SET UserName = :username, Phone = :phone, BirthDate = :birth_date, RuleID = :rule_id
                     WHERE UserID = :user_id'
                );
                $updateUser->execute([
                    ':username' => $username,
                    ':phone' => $phone === '' ? null : $phone,
                    ':birth_date' => $birthDate === '' ? null : $birthDate,
                    ':rule_id' => $ruleId,
                    ':user_id' => $targetUserId,
                ]);

                if ($targetUserId === $currentUserId) {
                    $_SESSION['user']['UserName'] = $username;
                    $_SESSION['user']['RuleID'] = $ruleId;
                    $_SESSION['user']['RuleName'] = $selectedRole['RuleName'];
                }

                header('Location: setting.php?updated=1');
                exit();
            }
        }
    } else {
        if ($targetUserId === $currentUserId) {
            $errorMessage = 'You cannot delete the account you are currently signed in with.';
        } else {
            $targetStatement = $con->prepare(
                'SELECT users.UserID, rules.RuleName
                 FROM users
                 LEFT JOIN rules ON rules.RuleID = users.RuleID
                 WHERE users.UserID = :user_id
                 LIMIT 1'
            );
            $targetStatement->execute([':user_id' => $targetUserId]);
            $targetUser = $targetStatement->fetch(PDO::FETCH_ASSOC);

            if ($targetUser === false) {
                $errorMessage = 'That user account could not be found.';
            } elseif (
                strtolower(preg_replace('/[^a-z0-9]/i', '', (string) ($targetUser['RuleName'] ?? ''))) === 'admin'
                && (int) $con->query(
                    "SELECT COUNT(*)
                     FROM users
                     INNER JOIN rules ON rules.RuleID = users.RuleID
                     WHERE LOWER(rules.RuleName) = 'admin'"
                )->fetchColumn() <= 1
            ) {
                $errorMessage = 'You cannot delete the last administrator account.';
            } else {
                try {
                    $con->beginTransaction();

                    $deleteLogins = $con->prepare('DELETE FROM user_login WHERE UserID = :user_id');
                    $deleteLogins->execute([':user_id' => $targetUserId]);

                    $deleteUser = $con->prepare('DELETE FROM users WHERE UserID = :user_id');
                    $deleteUser->execute([':user_id' => $targetUserId]);

                    if ($deleteUser->rowCount() !== 1) {
                        throw new RuntimeException('The selected user account was not deleted.');
                    }

                    $con->commit();
                    header('Location: setting.php?deleted=1');
                    exit();
                } catch (PDOException $exception) {
                    if ($con->inTransaction()) {
                        $con->rollBack();
                    }

                    if ($exception->getCode() !== '23000') {
                        throw $exception;
                    }

                    $errorMessage = 'This user is linked to other store records and cannot be deleted.';
                }
            }
        }
    }
}

if ($editUserId === '' && isset($_GET['edit_user']) && is_string($_GET['edit_user'])) {
    $editUserId = trim($_GET['edit_user']);
}

if ($editUserId !== '' && $canManageUsers) {
    $editUserStatement = $con->prepare(
        'SELECT UserID, UserName, Phone, BirthDate, RuleID
         FROM users
         WHERE UserID = :user_id
         LIMIT 1'
    );
    $editUserStatement->execute([':user_id' => $editUserId]);
    $editUser = $editUserStatement->fetch(PDO::FETCH_ASSOC);

    if ($editUser === false) {
        $editUser = null;
        $errorMessage = 'That user account could not be found.';
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset(
        $_POST['username'],
        $_POST['password'],
        $_POST['confirm_password'],
        $_POST['rule_id'],
        $_POST['csrf_token']
    )
    && is_string($_POST['username'])
    && is_string($_POST['password'])
    && is_string($_POST['confirm_password'])
    && is_string($_POST['rule_id'])
    && is_string($_POST['csrf_token'])
) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $ruleId = $_POST['rule_id'];
    $birthDate = isset($_POST['birth_date']) && is_string($_POST['birth_date'])
        ? trim($_POST['birth_date'])
        : '';
    $phone = isset($_POST['phone']) && is_string($_POST['phone'])
        ? trim($_POST['phone'])
        : '';

    if (!hash_equals($csrfToken, $_POST['csrf_token'])) {
        $errorMessage = 'Your session expired. Please try again.';
    } elseif ($username === '' || strlen($username) > 50) {
        $errorMessage = 'Enter a username of 1 to 50 characters.';
    } elseif (strlen($password) < 8) {
        $errorMessage = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'The passwords do not match.';
    } elseif (strlen($phone) > 20) {
        $errorMessage = 'Phone number must be no more than 20 characters.';
    } else {
        $selectedRole = null;
        foreach ($roles as $role) {
            if ($role['RuleID'] === $ruleId) {
                $selectedRole = $role;
                break;
            }
        }

        if ($selectedRole === null) {
            $errorMessage = 'Select a valid role.';
        } elseif ($birthDate !== '') {
            $date = DateTime::createFromFormat('!Y-m-d', $birthDate);
            if (!$date || $date->format('Y-m-d') !== $birthDate) {
                $errorMessage = 'Enter a valid birth date.';
            }
        }

        if ($errorMessage === null) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                throw new RuntimeException('Unable to hash the user password.');
            }

            $insert = $con->prepare(
                'INSERT INTO users (UserID, UserName, Password, BirthDate, Phone, RuleID)
                 VALUES (:user_id, :username, :password, :birth_date, :phone, :rule_id)'
            );
            $usernameExists = $con->prepare(
                'SELECT 1 FROM users WHERE UserName = :username LIMIT 1'
            );

            for ($attempt = 0; $attempt < 5; $attempt++) {
                $nextIdQuery = $con->query(
                    "SELECT COALESCE(MAX(CAST(SUBSTRING(UserID, 4) AS UNSIGNED)), 0) + 1
                     FROM users
                     WHERE UserID REGEXP '^UJW[0-9]+$'"
                );
                $nextId = (int) $nextIdQuery->fetchColumn();
                $userId = 'UJW' . str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);

                if (strlen($userId) > 10) {
                    throw new RuntimeException('Unable to generate a user ID within the database column limit.');
                }

                try {
                    $insert->execute([
                        ':user_id' => $userId,
                        ':username' => $username,
                        ':password' => $hashedPassword,
                        ':birth_date' => $birthDate === '' ? null : $birthDate,
                        ':phone' => $phone === '' ? null : $phone,
                        ':rule_id' => $ruleId,
                    ]);
                    header('Location: setting.php?created=1&user_id=' . rawurlencode($userId));
                    exit();
                } catch (PDOException $exception) {
                    if ($exception->getCode() !== '23000') {
                        throw $exception;
                    }

                    $usernameExists->execute([':username' => $username]);
                    if ($usernameExists->fetchColumn() !== false) {
                        $errorMessage = 'That username is already in use.';
                        break;
                    }
                }
            }

            if ($errorMessage === null) {
                throw new RuntimeException('Unable to generate a unique user ID after several attempts.');
            }
        }
    }
}

$usersStatement = $con->query(
    'SELECT users.UserID, users.UserName, users.RegistrationTime, users.BirthDate, users.Phone, rules.RuleName,
            login_summary.LastLoginTime, COALESCE(login_summary.LoginCount, 0) AS LoginCount
     FROM users
     LEFT JOIN rules ON rules.RuleID = users.RuleID
     LEFT JOIN (
         SELECT UserID, MAX(LoginTime) AS LastLoginTime, MAX(LoginCount) AS LoginCount
         FROM user_login
         GROUP BY UserID
     ) AS login_summary ON login_summary.UserID = users.UserID
     ORDER BY users.id DESC'
);
$users = $usersStatement->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="users-page">
    <div class="users-topbar">
        <a class="users-back-link" href="<?php echo htmlspecialchars(app_base_path() . '/settings/', ENT_QUOTES, 'UTF-8'); ?>" aria-label="Go back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>
    <header class="users-hero">
        <div class="users-hero-copy">
            <span class="users-eyebrow"><i class="fa-solid fa-gem"></i> TEAM & ACCESS</span>
            <h1>User Management</h1>
            <p>Create staff accounts and assign the right role for your store.</p>
        </div>
        <a class="users-hero-button" href="#add-user">
            <i class="fa-solid fa-user-plus"></i>
            Add new user
        </a>
    </header>

<?php if (isset($_GET['created'])): ?>
    <div class="users-notice users-notice-success" role="status">
        <i class="fa-solid fa-circle-check"></i>
        User account<?php if (isset($_GET['user_id']) && is_string($_GET['user_id'])): ?>
            <strong><?php echo htmlspecialchars($_GET['user_id'], ENT_QUOTES, 'UTF-8'); ?></strong>
        <?php endif; ?> created successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>
    <div class="users-notice users-notice-success" role="status">
        <i class="fa-solid fa-circle-check"></i>
        User account updated successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="users-notice users-notice-success" role="status">
        <i class="fa-solid fa-circle-check"></i>
        User account deleted successfully.
    </div>
<?php endif; ?>

<?php if ($errorMessage !== null): ?>
    <div class="users-notice users-notice-error" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if ($editUser !== null && $canManageUsers): ?>
<section class="users-card users-form-card users-edit-card" id="edit-user">
    <div class="users-card-heading">
        <div class="users-heading-icon"><i class="fa-solid fa-user-pen"></i></div>
        <div>
            <h2>Edit team member</h2>
            <p>Update account details and access role.</p>
        </div>
    </div>

    <form class="users-form" method="POST" action="setting.php#edit-user">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="user_action" value="edit">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string) $editUser['UserID'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="users-field-grid">
            <div class="users-field">
                <label for="edit_username">Username <span class="required-mark">*</span></label>
                <input id="edit_username" name="username" type="text" maxlength="50" required value="<?php echo htmlspecialchars((string) ($_POST['username'] ?? $editUser['UserName']), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="users-field">
                <label for="edit_birth_date">Birth date</label>
                <input id="edit_birth_date" name="birth_date" type="date" value="<?php echo htmlspecialchars((string) ($_POST['birth_date'] ?? $editUser['BirthDate'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="users-field">
                <label for="edit_phone">Phone number</label>
                <input id="edit_phone" name="phone" type="tel" maxlength="20" value="<?php echo htmlspecialchars((string) ($_POST['phone'] ?? $editUser['Phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="users-field">
                <label for="edit_rule_id">Account role <span class="required-mark">*</span></label>
                <div class="users-role-control">
                    <i class="fa-solid fa-shield-halved"></i>
                    <select id="edit_rule_id" name="rule_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars((string) $role['RuleID'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($_POST['rule_id'] ?? $editUser['RuleID']) === (string) $role['RuleID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) $role['RuleName'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="users-form-actions">
            <a class="users-cancel-link" href="setting.php">Cancel</a>
            <button class="users-submit" type="submit">Save changes <i class="fa-solid fa-floppy-disk"></i></button>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="users-card users-form-card" id="add-user">
    <div class="users-card-heading">
        <div class="users-heading-icon"><i class="fa-solid fa-user-plus"></i></div>
        <div>
            <h2>Add a team member</h2>
            <p>Enter account details and choose the access role.</p>
        </div>
    </div>

    <?php if ($roles === []): ?>
        <div class="users-empty-role">
            <i class="fa-solid fa-shield-halved"></i>
            <p>No roles are configured yet. Add a role to the <code>rules</code> table before creating users.</p>
        </div>
    <?php else: ?>
        <form class="users-form" method="POST" action="setting.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="users-form-section">
                <div class="users-section-title">
                    <span>01</span>
                    <div>
                        <h3>Personal information</h3>
                        <p>Basic details used to identify this account.</p>
                    </div>
                </div>

                <div class="users-id-note">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    A unique user ID will be generated automatically.
                </div>

                <div class="users-field-grid">
                    <div class="users-field">
                        <label for="username">Username <span class="required-mark">*</span></label>
                        <input id="username" name="username" type="text" maxlength="50" placeholder="Enter a username" autocomplete="username" required>
                    </div>
                    <div class="users-field">
                        <label for="birth_date">Birth date <span class="optional-mark">Optional</span></label>
                        <input id="birth_date" name="birth_date" type="date">
                    </div>
                    <div class="users-field">
                        <label for="phone">Phone number <span class="optional-mark">Optional</span></label>
                        <input id="phone" name="phone" type="tel" maxlength="20" placeholder="Enter phone number" autocomplete="tel">
                    </div>
                </div>
            </div>

            <div class="users-form-section">
                <div class="users-section-title">
                    <span>02</span>
                    <div>
                        <h3>Sign-in details</h3>
                        <p>Set a secure password for the new account.</p>
                    </div>
                </div>

                <div class="users-field-grid">
                    <div class="users-field">
                        <label for="password">Password <span class="required-mark">*</span></label>
                        <input id="password" name="password" type="password" minlength="8" placeholder="At least 8 characters" autocomplete="new-password" required>
                        <small>Password is securely hashed before it is saved.</small>
                    </div>
                    <div class="users-field">
                        <label for="confirm_password">Confirm password <span class="required-mark">*</span></label>
                        <input id="confirm_password" name="confirm_password" type="password" minlength="8" placeholder="Enter the password again" autocomplete="new-password" required>
                    </div>
                </div>
            </div>

            <div class="users-form-section users-role-section">
                <div class="users-section-title">
                    <span>03</span>
                    <div>
                        <h3>Role & permissions</h3>
                        <p>Choose which area of the store this user can access.</p>
                    </div>
                </div>

                <div class="users-field users-role-field">
                    <label for="rule_id">Account role <span class="required-mark">*</span></label>
                    <div class="users-role-control">
                        <i class="fa-solid fa-shield-halved"></i>
                        <select id="rule_id" name="rule_id" required>
                            <option value="">Select a role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['RuleID'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($role['RuleName'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="users-form-actions">
                <p><i class="fa-solid fa-lock"></i> Passwords are never shown in the user list.</p>
                <button class="users-submit" type="submit">
                    Create user account
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </form>
    <?php endif; ?>
</section>

<section class="users-card users-directory">
    <div class="users-directory-heading">
        <div class="users-card-heading">
            <div class="users-heading-icon users-heading-icon-light"><i class="fa-solid fa-users"></i></div>
            <div>
                <h2>Team directory</h2>
                <p><?php echo count($users); ?> <?php echo count($users) === 1 ? 'account' : 'accounts'; ?> in your store</p>
            </div>
        </div>
        <label class="users-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="user-search" type="search" placeholder="Search users" aria-label="Search users">
        </label>
    </div>
    <div class="users-table-wrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Team member</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Last login</th>
                    <th>Login count</th>
                    <th>Contact</th>
                    <?php if ($canManageUsers): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($users === []): ?>
                    <tr class="users-empty-row"><td colspan="<?php echo $canManageUsers ? '8' : '7'; ?>">No user accounts yet. Add your first team member above.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="users-table-row">
                            <td><span class="users-id"><?php echo htmlspecialchars((string) $user['UserID'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td>
                                <div class="users-member">
                                    <span class="users-avatar"><?php echo htmlspecialchars(strtoupper(substr((string) $user['UserName'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span><?php echo htmlspecialchars((string) $user['UserName'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </td>
                            <td><span class="users-role-badge"><?php echo htmlspecialchars((string) ($user['RuleName'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><?php echo htmlspecialchars((string) ($user['RegistrationTime'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if (!empty($user['LastLoginTime'])): ?>
                                    <div class="users-login-time">
                                        <i class="fa-regular fa-clock"></i>
                                        <span><?php echo htmlspecialchars((string) $user['LastLoginTime'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="users-never-logged-in">Never</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="users-login-count"><?php echo (int) $user['LoginCount']; ?></span></td>
                            <td>
                                <div class="users-contact">
                                    <span><?php echo htmlspecialchars((string) ($user['Phone'] ?: 'No phone'), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (!empty($user['BirthDate'])): ?>
                                        <small>Born <?php echo htmlspecialchars((string) $user['BirthDate'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if ($canManageUsers): ?>
                                <td>
                                    <div class="users-row-actions">
                                        <a class="users-action-edit" href="setting.php?edit_user=<?php echo rawurlencode((string) $user['UserID']); ?>#edit-user">
                                            <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </a>
                                        <?php if ((string) $user['UserID'] !== $currentUserId): ?>
                                            <form method="POST" action="setting.php" onsubmit="return confirm('Delete this user account? This action cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="user_action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string) $user['UserID'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <button class="users-action-delete" type="submit">
                                                    <i class="fa-solid fa-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
</div>

<script>
document.getElementById('user-search').addEventListener('input', function () {
    var query = this.value.trim().toLowerCase();
    document.querySelectorAll('.users-table-row').forEach(function (row) {
        row.hidden = !row.textContent.toLowerCase().includes(query);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
