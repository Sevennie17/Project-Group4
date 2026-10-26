<?php
require_once __DIR__ . '/auth.php';
require_authenticated_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Jewelry Shop</title>

    <!-- Main CSS -->
    <link
        rel="stylesheet"
        href="<?php echo htmlspecialchars(app_base_path() . '/assets/css/style.css?v=' . filemtime(__DIR__ . '/../assets/css/style.css'), ENT_QUOTES, 'UTF-8'); ?>"
    >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">

    <!-- Page CSS -->
    <?php if (isset($page_css) && !empty($page_css)) : ?>
        <link
            rel="stylesheet"
            href="<?php
                $pageCssHref = $page_css;
                $pageCssBasePath = app_base_path();
                if (strpos($pageCssHref, $pageCssBasePath . '/') === 0) {
                    $pageCssFile = __DIR__ . '/../' . str_replace(
                        '/',
                        DIRECTORY_SEPARATOR,
                        substr($pageCssHref, strlen($pageCssBasePath) + 1)
                    );
                    if (is_file($pageCssFile)) {
                        $pageCssHref .= '?v=' . filemtime($pageCssFile);
                    }
                }
                echo htmlspecialchars($pageCssHref, ENT_QUOTES, 'UTF-8');
            ?>"
        >
    <?php endif; ?>

</head>


<body>

    <?php include __DIR__ . '/navbar.php'; ?>


    <!-- TOP HEADER -->
    <header class="top-header">

        <button
            class="menu-btn"
            id="menuBtn"
            type="button"
        >

            <i class="fa-solid fa-bars"></i>

        </button>


        <h2>Jewelry Shop</h2>


        <div class="header-right">

            <span>
                <i class="fa-solid fa-bell"></i>
            </span>

            <a
                class="header-profile-link"
                href="<?php echo htmlspecialchars(app_base_path() . '/profile/', ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="View profile"
                title="View profile"
            >
                <i class="fa-solid fa-user"></i>
            </a>

        </div>

    </header>


    <!-- MAIN CONTENT -->
    <main class="main-content">