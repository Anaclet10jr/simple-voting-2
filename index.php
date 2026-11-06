<?php
require __DIR__.'/config.php'; require __DIR__.'/functions.php';
verify_csrf();
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='login') {
    $email = filter_var(trim($_POST['email']??''), FILTER_VALIDATE_EMAIL);
    $stmt = $pdo->prepare('SELECT id,full_name,email,password_hash,role FROM users WHERE email=?');
    $stmt->execute([$email ?: '']);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password']??'', $u['password_hash'])) {
        session_regenerate_id(true);
        unset($u['password_hash']); $_SESSION['user']=$u;
        header('Location: '.($u['role']==='admin'?'admin.php':'voter.php')); exit;
    }
    flash('Invalid email or password.','error');
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='register') {
    $name=trim($_POST['name']??''); $email=filter_var(trim($_POST['email']??''),FILTER_VALIDATE_EMAIL); $pw=$_POST['password']??'';
    if ($name==='' || !$email || strlen($pw)<10) flash('Enter a name, valid email and password of at least 10 characters.','error');
    else {
        try {
            $s=$pdo->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,'voter')");
            $s->execute([$name,$email,password_hash($pw,PASSWORD_DEFAULT)]);
            flash('Registration successful. You can now sign in.');
        } catch(PDOException $ex) { flash('That email is already registered.','error'); }
    }
}
if (isset($_GET['logout'])) { $_SESSION=[]; session_destroy(); header('Location: index.php'); exit; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Online Voting System</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<header class="bg-slate-950 text-white"><div class="max-w-6xl mx-auto px-5 py-5 flex justify-between"><strong class="text-xl">Vote<span class="text-blue-400">Secure</span></strong><span class="text-slate-300 text-sm">Online Voting Management</span></div></header>
<main class="max-w-5xl mx-auto p-5 py-12">
<?php show_flash(); ?>
<div class="grid md:grid-cols-2 gap-6">
<section class="bg-white rounded-2xl shadow-sm p-7"><h1 class="text-2xl font-bold">Welcome back</h1><p class="text-slate-500 mt-1 mb-6">Sign in to access your voting account.</p>
<form method="post" class="space-y-4"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="login">
<label class="block text-sm font-medium">Email<input required type="email" name="email" class="mt-1 w-full rounded-lg border p-3"></label>
<label class="block text-sm font-medium">Password<input required type="password" name="password" class="mt-1 w-full rounded-lg border p-3"></label>
<button class="w-full rounded-lg bg-blue-700 text-white p-3 font-semibold hover:bg-blue-800">Sign in</button></form>
<p class="text-xs text-slate-500 mt-5">First time? The administrator must complete <a class="text-blue-700 underline" href="setup.php">initial setup</a>.</p></section>
<section class="bg-white rounded-2xl shadow-sm p-7"><h2 class="text-2xl font-bold">Voter registration</h2><p class="text-slate-500 mt-1 mb-6">Create a voter account to participate in published elections.</p>
<form method="post" class="space-y-4"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="register">
<label class="block text-sm font-medium">Full name<input required name="name" maxlength="120" class="mt-1 w-full rounded-lg border p-3"></label>
<label class="block text-sm font-medium">Email<input required type="email" name="email" class="mt-1 w-full rounded-lg border p-3"></label>
<label class="block text-sm font-medium">Password (10+ characters)<input required minlength="10" type="password" name="password" class="mt-1 w-full rounded-lg border p-3"></label>
<button class="w-full rounded-lg border border-blue-700 text-blue-700 p-3 font-semibold hover:bg-blue-50">Register as voter</button></form></section>
</div><p class="text-center text-xs text-slate-500 mt-8">Demonstration voting platform. Use appropriate legal, identity and security reviews before any binding election.</p></main></body></html>
