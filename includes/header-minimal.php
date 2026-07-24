<?php
// Mehmaan Hub - Minimal Header for Auth Pages (no navbar)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mehmaan Hub - Find Your Perfect Stay in Pakistan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?php echo url('/assets/css/style.css'); ?>" rel="stylesheet">
    <script>var SITE_URL = '<?php echo SITE_URL; ?>';</script>
</head>
<body>
<?php
$flashSuccess = flash('success');
$flashError = flash('error');
?>
<?php if ($flashSuccess): ?>
<div class="alert alert-success" style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;min-width:300px;max-width:90%;box-shadow:var(--shadow-xl);border-radius:var(--radius);padding:1rem 1.25rem;font-weight:600;" id="flashAlert">
    <i class="bi bi-check-circle"></i> <?php echo e($flashSuccess); ?>
</div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="alert alert-danger" style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;min-width:300px;max-width:90%;box-shadow:var(--shadow-xl);border-radius:var(--radius);padding:1rem 1.25rem;font-weight:600;" id="flashAlert">
    <i class="bi bi-exclamation-circle"></i> <?php echo e($flashError); ?>
</div>
<?php endif; ?>
