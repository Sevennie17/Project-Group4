<?php
$page_css = '/Jewelry-app/settings/setting.css';
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

$basePath = app_base_path();
include __DIR__ . '/../includes/header.php';
?>

<div class="settings-page">
    <header class="settings-hero">
        <div class="settings-hero-copy">
            <span class="settings-eyebrow"><i class="fa-solid fa-gem"></i> STORE CONTROL</span>
            <h1>Settings</h1>
            <p>Manage your jewelry shop operations, access, and daily preferences from one place.</p>
        </div>
    </header>

    <section class="settings-summary">
        <div class="settings-stat">
            <span class="settings-stat-label">Active users</span>
            <strong>18</strong>
        </div>
        <div class="settings-stat">
            <span class="settings-stat-label">Open alerts</span>
            <strong>04</strong>
        </div>
        <div class="settings-stat">
            <span class="settings-stat-label">Auto backup</span>
            <strong>Daily</strong>
        </div>
        <div class="settings-stat">
            <span class="settings-stat-label">System status</span>
            <strong>Online</strong>
        </div>
    </section>

    <section class="settings-grid" aria-label="Store settings features">
        <article class="settings-card">
            <div class="settings-card-icon settings-icon-gold">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div class="settings-card-body">
                <h2>User Accounts</h2>
                <p>Create staff accounts, manage roles, and control access levels.</p>
            </div>
            <a class="settings-card-link" href="setting.php">Manage users</a>
        </article>

        <article class="settings-card">
            <div class="settings-card-icon settings-icon-sand">
                <i class="fa-solid fa-shop"></i>
            </div>
            <div class="settings-card-body">
                <h2>Store Profile</h2>
                <p>Update your business name, contact details, and branding.</p>
            </div>
            <a class="settings-card-link" href="<?php echo htmlspecialchars($basePath . '/profile/', ENT_QUOTES, 'UTF-8'); ?>">Edit profile</a>
        </article>

        <article class="settings-card">
            <div class="settings-card-icon settings-icon-amber">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="settings-card-body">
                <h2>Security</h2>
                <p>Review login settings, passwords, and account protection rules.</p>
            </div>
            <a class="settings-card-link" href="#security">Open security</a>
        </article>

        <article class="settings-card">
            <div class="settings-card-icon settings-icon-rose">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="settings-card-body">
                <h2>Notifications</h2>
                <p>Configure alerts for sales, low stock, returns, and daily reports.</p>
            </div>
            <a class="settings-card-link" href="<?php echo htmlspecialchars($basePath . '/notifications/', ENT_QUOTES, 'UTF-8'); ?>">Manage alerts</a>
        </article>

        <article class="settings-card">
            <div class="settings-card-icon settings-icon-olive">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="settings-card-body">
                <h2>Inventory Rules</h2>
                <p>Set reorder thresholds, alert levels, and stock review preferences.</p>
            </div>
            <a class="settings-card-link" href="#inventory">Adjust rules</a>
        </article>

        <article class="settings-card">
            <div class="settings-card-icon settings-icon-sky">
                <i class="fa-solid fa-database"></i>
            </div>
            <div class="settings-card-body">
                <h2>Backup & Data</h2>
                <p>Manage recovery plans, scheduled exports, and historical retention.</p>
            </div>
            <a class="settings-card-link" href="#backup">Review backups</a>
        </article>
    </section>

    <section class="settings-panel" id="security">
        <div class="settings-panel-header">
            <div>
                <span class="settings-panel-kicker">SECURITY</span>
                <h2>Operational preferences</h2>
            </div>
        </div>

        <div class="settings-toggle-list">
            <label class="settings-toggle-row">
                <div>
                    <strong>Require two-factor login</strong>
                    <small>Extra protection for staff access to the sales and inventory modules.</small>
                </div>
                <input type="checkbox" checked>
            </label>

            <label class="settings-toggle-row">
                <div>
                    <strong>Low-stock alerts</strong>
                    <small>Send alerts when popular items or raw materials are running low.</small>
                </div>
                <input type="checkbox" checked>
            </label>

            <label class="settings-toggle-row">
                <div>
                    <strong>Daily sales summary</strong>
                    <small>Receive a summary of sales, refunds, and best sellers at 8:00 AM.</small>
                </div>
                <input type="checkbox" checked>
            </label>

            <label class="settings-toggle-row">
                <div>
                    <strong>Auto data backup</strong>
                    <small>Back up store records every night to preserve recent sales data.</small>
                </div>
                <input type="checkbox" checked>
            </label>
        </div>
    </section>

    <section class="settings-panel" id="inventory">
        <div class="settings-panel-header">
            <div>
                <span class="settings-panel-kicker">INVENTORY</span>
                <h2>Stock control rules</h2>
            </div>
        </div>

        <div class="settings-toggle-list">
            <label class="settings-toggle-row">
                <div>
                    <strong>Minimum stock threshold</strong>
                    <small>Warn when the available quantity falls below the configured reorder level.</small>
                </div>
                <input type="checkbox" checked>
            </label>

            <label class="settings-toggle-row">
                <div>
                    <strong>Supplier follow-up reminders</strong>
                    <small>Create reminder tasks for delayed inventory deliveries and restocks.</small>
                </div>
                <input type="checkbox" checked>
            </label>

            <label class="settings-toggle-row">
                <div>
                    <strong>Inventory review checklist</strong>
                    <small>Prompt staff to verify physical counts before the end of each week.</small>
                </div>
                <input type="checkbox">
            </label>
        </div>
    </section>

    <section class="settings-panel" id="backup">
        <div class="settings-panel-header">
            <div>
                <span class="settings-panel-kicker">DATA</span>
                <h2>Backup & recovery</h2>
            </div>
        </div>

        <div class="settings-toggle-list">
            <label class="settings-toggle-row">
                <div>
                    <strong>Nightly database backup</strong>
                    <small>Generate a full automatic backup every night at 2:00 AM.</small>
                </div>
                <input type="checkbox" checked>
            </label>

            <label class="settings-toggle-row">
                <div>
                    <strong>Encrypted local copies</strong>
                    <small>Keep a protected archive for sensitive store and customer data.</small>
                </div>
                <input type="checkbox" checked>
            </label>

            <label class="settings-toggle-row">
                <div>
                    <strong>30-day retention</strong>
                    <small>Keep recent backups available for quick recovery and audit review.</small>
                </div>
                <input type="checkbox" checked>
            </label>
        </div>
    </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>