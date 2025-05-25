<?php
// Get the current script's directory path
$current_dir = dirname($_SERVER['SCRIPT_NAME']);

// Determine the project root based on current location
if (strpos($current_dir, '/admin') !== false) {
    $base_path = dirname($current_dir);
} elseif (strpos($current_dir, '/judge') !== false) {
    $base_path = dirname($current_dir);
} elseif (strpos($current_dir, '/includes') !== false) {
    $base_path = dirname($current_dir);
} else {
    $base_path = $current_dir;
}

// Ensure base path ends with slash and starts with slash
$base_path = '/' . trim($base_path, '/') . '/';

// Build full base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host . $base_path;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Judging System'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-title">
                <a href="<?php echo $base_url; ?>">Judging System</a>
            </h1>
            <ul class="nav-menu">
                <li><a href="<?php echo $base_url; ?>">Scoreboard</a></li>
                <li><a href="<?php echo $base_url; ?>admin/">Admin</a></li>
                <li><a href="<?php echo $base_url; ?>judge/">Judge Portal</a></li>
            </ul>
        </div>
    </nav>
    
    <main class="main-content">
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

