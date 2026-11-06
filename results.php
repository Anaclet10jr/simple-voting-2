<?php
require __DIR__.'/config.php'; require __DIR__.'/functions.php'; require_login();
$id=(int)($_GET['id']??0);
$s=$pdo->prepare('SELECT * FROM elections WHERE id=?');$s->execute([$id]);$election=$s->fetch();
if(!$election){http_response_code(404);exit('Election not found.');}
if(current_user()['role']!=='admin' && $election['status']!=='closed'){http_response_code(403);exit('Results are available to voters after the election closes.');}
$s=$pdo->prepare('SELECT c.full_name,c.manifesto,COUNT(v.id) vote_count FROM candidates c LEFT JOIN votes v ON v.candidate_id=c.id AND v.election_id=c.election_id WHERE c.election_id=? GROUP BY c.id ORDER BY vote_count DESC,c.full_name');$s->execute([$id]);$rows=$s->fetchAll();$total=array_sum(array_column($rows,'vote_count'));
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Election results</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-100"><main class="max-w-3xl mx-auto p-5"><a class="text-blue-700 underline" href="<?=current_user()['role']==='admin'?'admin.php':'voter.php'?>">← Back</a><section class="bg-white rounded-2xl p-7 mt-5"><h1 class="text-2xl font-bold"><?=e($election['title'])?> — Results</h1><p class="text-slate-500 mt-1">Election status: <?=e($election['status'])?> · Total votes: <?=$total?></p>
<?php foreach($rows as $r): $pct=$total?round($r['vote_count']/$total*100,1):0; ?><div class="mt-6"><div class="flex justify-between gap-3"><b><?=e($r['full_name'])?></b><span><?=$r['vote_count']?> votes (<?=$pct?>%)</span></div><div class="h-3 bg-slate-200 rounded-full mt-2 overflow-hidden"><div class="h-full bg-blue-700 rounded-full" style="width:<?=$pct?>%"></div></div><p class="text-sm text-slate-500 mt-1"><?=e($r['manifesto'])?></p></div><?php endforeach; ?>
<?php if(!$rows): ?><p class="mt-5 text-slate-500">No candidates have been added.</p><?php endif; ?></section></main></body></html>
