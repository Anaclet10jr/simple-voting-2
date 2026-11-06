<?php
require __DIR__.'/config.php';
require __DIR__.'/functions.php';
$exists = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() > 0;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$exists) {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if ($name === '' || !$email || strlen($password) < 10) {
        $error = 'Enter a name, valid email and password of at least 10 characters.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,'admin')");
        try {
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $exists = true;
            flash('Administrator created. Sign in now.');
            header('Location: index.php'); exit;
        } catch (PDOException $ex) { $error = 'That email address is already registered.'; }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Initial setup</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4"><main class="bg-white rounded-2xl shadow p-8 w-full max-w-lg">
<h1 class="text-2xl font-bold text-slate-900">Voting System Setup</h1>
<?php if($exists): ?><p class="mt-4 text-green-700">An administrator already exists. For security, setup is disabled.</p><a class="inline-block mt-4 text-blue-700 underline" href="index.php">Go to login</a>
<?php else: ?>
<p class="text-slate-600 mt-2">Create the first administrator account.</p>
<?php if($error): ?><p class="bg-red-100 text-red-800 p-3 rounded mt-4"><?=e($error)?></p><?php endif; ?>
<form method="post" class="space-y-4 mt-5">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label class="block">Full name<input required name="name" class="w-full border rounded-lg p-3 mt-1"></label>
<label class="block">Email<input required type="email" name="email" class="w-full border rounded-lg p-3 mt-1"></label>
<label class="block">Password (minimum 10 characters)<input required minlength="10" type="password" name="password" class="w-full border rounded-lg p-3 mt-1"></label>
<button class="bg-blue-700 text-white rounded-lg px-5 py-3 w-full">Create administrator</button>
</form><?php endif; ?></main></body></html>
