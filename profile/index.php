<?php
$page_css = '/Jewelry-app/profile/profile.css';
require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user']['UserID'];
$errorMessage = null;
$isEditing = isset($_GET['edit']) || $_SERVER['REQUEST_METHOD'] === 'POST';
$csrfToken = $_SESSION['profile_csrf_token'] ??= bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
        ? $_POST['csrf_token']
        : '';
    $username = isset($_POST['username']) && is_string($_POST['username'])
        ? trim($_POST['username'])
        : '';
    $phone = isset($_POST['phone']) && is_string($_POST['phone'])
        ? trim($_POST['phone'])
        : '';
    $birthDate = isset($_POST['birth_date']) && is_string($_POST['birth_date'])
        ? trim($_POST['birth_date'])
        : '';

    if (!hash_equals($csrfToken, $submittedToken)) {
        $errorMessage = 'Your session expired. Please try again.';
    } elseif ($username === '' || strlen($username) > 50) {
        $errorMessage = 'Username must be between 1 and 50 characters.';
    } elseif (strlen($phone) > 20) {
        $errorMessage = 'Phone number must be no more than 20 characters.';
    } elseif ($birthDate !== '' && (
        ($date = DateTime::createFromFormat('!Y-m-d', $birthDate)) === false
        || $date->format('Y-m-d') !== $birthDate
    )) {
        $errorMessage = 'Enter a valid birth date.';
    } else {
        $usernameCheck = $con->prepare(
            'SELECT 1 FROM users WHERE UserName = :username AND UserID <> :user_id LIMIT 1'
        );
        $usernameCheck->execute([
            ':username' => $username,
            ':user_id' => $userId,
        ]);

        if ($usernameCheck->fetchColumn() !== false) {
            $errorMessage = 'That username is already in use.';
        } else {
            $updateProfile = $con->prepare(
                'UPDATE users
                 SET UserName = :username, Phone = :phone, BirthDate = :birth_date
                 WHERE UserID = :user_id'
            );
            $updateProfile->execute([
                ':username' => $username,
                ':phone' => $phone === '' ? null : $phone,
                ':birth_date' => $birthDate === '' ? null : $birthDate,
                ':user_id' => $userId,
            ]);

            $_SESSION['user']['UserName'] = $username;
            header('Location: ' . app_base_path() . '/profile/?updated=1');
            exit();
        }
    }
}

$profileStatement = $con->prepare(
    'SELECT users.UserID, users.UserName, users.RegistrationTime, users.BirthDate, users.Phone, rules.RuleName,
            login_summary.LastLoginTime, COALESCE(login_summary.LoginCount, 0) AS LoginCount
     FROM users
     LEFT JOIN rules ON rules.RuleID = users.RuleID
     LEFT JOIN (
         SELECT UserID, MAX(LoginTime) AS LastLoginTime, MAX(LoginCount) AS LoginCount
         FROM user_login
         GROUP BY UserID
     ) AS login_summary ON login_summary.UserID = users.UserID
     WHERE users.UserID = :user_id
     LIMIT 1'
);
$profileStatement->execute([':user_id' => $userId]);
$profile = $profileStatement->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($profile)) {
    $profile['UserName'] = $username;
    $profile['Phone'] = $phone;
    $profile['BirthDate'] = $birthDate;
}

$formatProfileValue = static function ($value) {
    if ($value === null || $value === '') {
        return 'Not provided';
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

include __DIR__ . '/../includes/header.php';
?>

<div class="profile-page">
    <div class="page-title">
        <h1>Profile</h1>
        <p>Your account information.</p>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="profile-message profile-message-success" role="status">Your profile has been updated.</div>
    <?php endif; ?>
    <?php if ($errorMessage !== null): ?>
        <div class="profile-message profile-message-error" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($profile === false): ?>
        <div class="card profile-card" role="alert">Your account information could not be found. Please sign in again or contact an administrator.</div>
    <?php else: ?>
      <?php if ($isEditing): ?>
        <section class="card profile-card" aria-labelledby="profile-details-heading">
            <div class="profile-card-heading">
                <span class="profile-avatar" aria-hidden="true">
                    <?php echo htmlspecialchars(strtoupper(substr((string) $profile['UserName'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <div>
                    <h2 id="profile-details-heading">Edit profile</h2>
                    <p>Update your account details.</p>
                </div>
            </div>

            <form class="profile-form" method="post" action="<?php echo htmlspecialchars(app_base_path() . '/profile/', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="profile-field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" maxlength="50" autocomplete="username" required value="<?php echo htmlspecialchars((string) $profile['UserName'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="profile-field">
                    <label for="phone">Phone number</label>
                    <input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="<?php echo htmlspecialchars((string) ($profile['Phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="profile-field">
                    <label for="birth-date">Birth date</label>
                    <input id="birth-date" name="birth_date" type="date" value="<?php echo htmlspecialchars((string) ($profile['BirthDate'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="profile-actions">
                    <button class="btn btn-gold" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save changes</button>
                    <a class="btn btn-light" href="<?php echo htmlspecialchars(app_base_path() . '/profile/', ENT_QUOTES, 'UTF-8'); ?>">Cancel</a>
                </div>
            </form>
        </section>
      <?php else: ?>
        <section class="card profile-card" aria-labelledby="profile-details-heading">
        <div class="profile-card-heading">
            <span class="profile-avatar" aria-hidden="true">
                <?php echo htmlspecialchars(strtoupper(substr((string) $profile['UserName'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <div>
                <h2 id="profile-details-heading"><?php echo $formatProfileValue($profile['UserName']); ?></h2>
                <p><?php echo $formatProfileValue($profile['RuleName']); ?></p>
            </div>
        </div>

        <dl class="profile-details">
            <div>
                <dt>User ID</dt>
                <dd><?php echo $formatProfileValue($profile['UserID']); ?></dd>
            </div>
            <div>
                <dt>Username</dt>
                <dd><?php echo $formatProfileValue($profile['UserName']); ?></dd>
            </div>
            <div>
                <dt>Phone number</dt>
                <dd><?php echo $formatProfileValue($profile['Phone']); ?></dd>
            </div>
            <div>
                <dt>Birth date</dt>
                <dd><?php echo $formatProfileValue($profile['BirthDate']); ?></dd>
            </div>
            <div>
                <dt>Member since</dt>
                <dd><?php echo $formatProfileValue($profile['RegistrationTime']); ?></dd>
            </div>
            <div>
                <dt>Last login</dt>
                <dd><?php echo $formatProfileValue($profile['LastLoginTime']); ?></dd>
            </div>
            <div>
                <dt>Successful logins</dt>
                <dd><?php echo $formatProfileValue($profile['LoginCount']); ?></dd>
            </div>
        </dl>
            <div class="profile-actions">
                <a class="btn btn-gold" href="<?php echo htmlspecialchars(app_base_path() . '/profile/?edit=1', ENT_QUOTES, 'UTF-8'); ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit profile</a>
                <form class="profile-logout-form" method="post" action="<?php echo htmlspecialchars(app_base_path() . '/profile/logout.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <button class="btn btn-light profile-logout-button" type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Log out</button>
                </form>
            </div>
        </section>
      <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
