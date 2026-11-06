<?php
require __DIR__.'/config.php'; require __DIR__.'/functions.php'; require_login();
if(current_user()['role']!=='voter'){header('Location: admin.php');exit;}
$uid=(int)current_user()['id'];
$sql="SELECT e.*, (SELECT COUNT(*) FROM votes v WHERE v.election_id=e.id AND v.voter_id=?) AS already_voted FROM elections e WHERE e.status='published' AND NOW() BETWEEN e.starts_at AND e.ends_at ORDER BY e.starts_at";
$s=$pdo->prepare($sql);$s->execute([$uid]);$elections=$s->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Voter portal</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-100 text-slate-800"><nav class="bg-slate-950 text-white px-6 py-4 flex justify-between"><b>VoteSecure / Voter</b><a href="index.php?logout=1" class="text-blue-300">Sign out</a></nav><main class="max-w-4xl mx-auto p-5"><h1 class="text-3xl font-bold">Hello, <?=e(current_user()['full_name'])?></h1><p class="text-slate-500 mt-1 mb-6">Available elections</p><?php show_flash(); ?>
<?php foreach($elections as $el): ?><article class="bg-white rounded-xl p-6 mb-4"><h2 class="text-xl font-bold"><?=e($el['title'])?></h2><p class="text-slate-600 my-2"><?=e($el['description'])?></p><p class="text-xs text-slate-500 mb-4">Closes: <?=e($el['ends_at'])?></p>
<?php if($el['already_voted']): ?><p class="text-green-700 font-semibold">Your vote has been recorded.</p><?php else: ?>
<form method="post" action="vote.php" class="space-y-3"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="election_id" value="<?=$el['id']?>">
<?php $c=$pdo->prepare('SELECT id,full_name,manifesto FROM candidates WHERE election_id=? ORDER BY full_name');$c->execute([$el['id']]);foreach($c as $candidate): ?><label class="flex gap-3 border rounded-lg p-3 cursor-pointer hover:bg-blue-50"><input required type="radio" name="candidate_id" value="<?=$candidate['id']?>"><span><b><?=e($candidate['full_name'])?></b><span class="block text-sm text-slate-500"><?=e($candidate['manifesto'])?></span></span></label><?php endforeach; ?>
<button onclick="return confirm('Submit your vote? You cannot change it afterwards.')" class="bg-blue-700 text-white px-5 py-3 rounded-lg">Submit vote</button></form><?php endif; ?></article><?php endforeach; ?>
<?php if(!$elections): ?><div class="bg-white rounded-xl p-6 text-slate-500">There are no active elections available at this time.</div><?php endif; ?></main></body></html>
