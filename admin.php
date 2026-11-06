<?php
require __DIR__.'/config.php'; require __DIR__.'/functions.php'; require_admin(); verify_csrf();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if ($action==='create_election') {
        $title=trim($_POST['title']??''); $desc=trim($_POST['description']??'');
        $start=str_replace('T',' ',$_POST['starts_at']??''); $end=str_replace('T',' ',$_POST['ends_at']??'');
        if($title==='' || strtotime($start)===false || strtotime($end)===false || strtotime($end)<=strtotime($start)) flash('Enter a title and a valid start/end time.','error');
        else { $s=$pdo->prepare("INSERT INTO elections(title,description,starts_at,ends_at,status) VALUES(?,?,?,?,'draft')"); $s->execute([$title,$desc,$start,$end]); flash('Election created. Add candidates below.'); }
    } elseif ($action==='add_candidate') {
        $eid=(int)($_POST['election_id']??0); $name=trim($_POST['name']??''); $manifesto=trim($_POST['manifesto']??'');
        $s=$pdo->prepare("SELECT id FROM elections WHERE id=? AND status='draft'"); $s->execute([$eid]);
        if(!$s->fetch() || $name==='') flash('Choose a draft election and enter a candidate name.','error');
        else { $s=$pdo->prepare('INSERT INTO candidates(election_id,full_name,manifesto) VALUES(?,?,?)'); $s->execute([$eid,$name,$manifesto]); flash('Candidate added.'); }
    } elseif ($action==='publish') {
        $eid=(int)($_POST['election_id']??0);
        $s=$pdo->prepare('SELECT COUNT(*) FROM candidates WHERE election_id=?'); $s->execute([$eid]);
        if((int)$s->fetchColumn()<2) flash('Add at least two candidates before publishing.','error');
        else { $s=$pdo->prepare("UPDATE elections SET status='published' WHERE id=? AND status='draft'"); $s->execute([$eid]); flash('Election published.'); }
    } elseif ($action==='close') {
        $s=$pdo->prepare("UPDATE elections SET status='closed' WHERE id=? AND status='published'"); $s->execute([(int)$_POST['election_id']]); flash('Election closed.');
    }
    header('Location: admin.php'); exit;
}
$elections=$pdo->query("SELECT e.*, (SELECT COUNT(*) FROM candidates c WHERE c.election_id=e.id) candidate_count, (SELECT COUNT(*) FROM votes v WHERE v.election_id=e.id) vote_count FROM elections e ORDER BY e.created_at DESC")->fetchAll();
$voters=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='voter'")->fetchColumn();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin dashboard</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-100 text-slate-800">
<nav class="bg-slate-950 text-white px-6 py-4 flex justify-between"><b>VoteSecure / Admin</b><a href="index.php?logout=1" class="text-blue-300">Sign out</a></nav>
<main class="max-w-6xl mx-auto p-5 space-y-6"><?php show_flash(); ?>
<h1 class="text-3xl font-bold">Administrator dashboard</h1>
<div class="grid sm:grid-cols-3 gap-4"><div class="bg-white p-5 rounded-xl"><p class="text-slate-500">Registered voters</p><b class="text-3xl"><?=$voters?></b></div><div class="bg-white p-5 rounded-xl"><p class="text-slate-500">Elections</p><b class="text-3xl"><?=count($elections)?></b></div><div class="bg-white p-5 rounded-xl"><p class="text-slate-500">Votes cast</p><b class="text-3xl"><?=array_sum(array_column($elections,'vote_count'))?></b></div></div>
<section class="bg-white p-6 rounded-xl"><h2 class="text-xl font-bold mb-4">Create election</h2><form method="post" class="grid md:grid-cols-2 gap-4"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="create_election">
<input required name="title" maxlength="180" placeholder="Election title" class="border rounded-lg p-3">
<textarea name="description" placeholder="Description" class="border rounded-lg p-3"></textarea>
<label class="text-sm">Starts at<input required type="datetime-local" name="starts_at" class="block w-full border rounded-lg p-3 mt-1"></label><label class="text-sm">Ends at<input required type="datetime-local" name="ends_at" class="block w-full border rounded-lg p-3 mt-1"></label>
<button class="md:col-span-2 bg-blue-700 text-white rounded-lg p-3">Create draft election</button></form></section>
<section class="space-y-4"><h2 class="text-xl font-bold">Manage elections</h2>
<?php foreach($elections as $el): ?><article class="bg-white rounded-xl p-5 shadow-sm"><div class="flex flex-wrap justify-between gap-3"><div><h3 class="font-bold text-lg"><?=e($el['title'])?></h3><p class="text-sm text-slate-500"><?=e($el['description'])?></p><p class="text-sm mt-2">Status: <b><?=e($el['status'])?></b> · Candidates: <?=$el['candidate_count']?> · Votes: <?=$el['vote_count']?></p></div>
<?php if($el['status']==='draft'): ?><form method="post" class="flex gap-2"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="publish"><input type="hidden" name="election_id" value="<?=$el['id']?>"><button class="bg-green-700 text-white px-4 py-2 rounded-lg">Publish</button></form><?php elseif($el['status']==='published'): ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="close"><input type="hidden" name="election_id" value="<?=$el['id']?>"><button class="bg-red-700 text-white px-4 py-2 rounded-lg">Close election</button></form><?php endif; ?></div>
<?php if($el['status']==='draft'): ?><form method="post" class="mt-4 grid md:grid-cols-3 gap-2"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add_candidate"><input type="hidden" name="election_id" value="<?=$el['id']?>"><input required name="name" placeholder="Candidate full name" class="border rounded-lg p-2"><input name="manifesto" placeholder="Short manifesto" class="border rounded-lg p-2"><button class="border border-blue-700 text-blue-700 rounded-lg p-2">Add candidate</button></form><?php endif; ?>
<a class="inline-block mt-3 text-blue-700 underline" href="results.php?id=<?=$el['id']?>">View results</a></article><?php endforeach; ?>
<?php if(!$elections): ?><p class="bg-white rounded-xl p-5 text-slate-500">No elections yet. Create your first election above.</p><?php endif; ?></section></main></body></html>
