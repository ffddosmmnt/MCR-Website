<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | MCR</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="<?= e($stylesheet) ?>">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="logo" href="index.php">MCR</a>
            <nav class="main-nav" aria-label="Main navigation">
                <?php foreach (['home' => ['index.php', 'Home'], 'about' => ['about.html', 'About'], 'music' => ['music.php', 'Music'], 'tour' => ['tour.php', 'Tour'], 'store' => ['showcase.html', 'Store'], 'news' => ['news.php', 'News']] as $key => [$href, $label]): ?>
                <a href="<?= e($href) ?>"<?= $key === $activePage ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="header-icons" aria-label="Utility navigation">
                <a href="music.php#lyrics">Search</a>
                <a href="showcase.html#bag">Bag</a>
            </div>
        </div>
    </header>
