<?php
require __DIR__.'/config.php'; require __DIR__.'/functions.php'; require_login(); verify_csrf();
if(current_user()['role']!=='voter'){http_response_code(403);exit('Only voters can vote.');}
$eid=(int)($_POST['election_id']??0);$cid=(int)($_POST['candidate_id']??0);$uid=(int)current_user()['id'];
try {
    $pdo->beginTransaction();
    $s=$pdo->prepare("SELECT id FROM elections WHERE id=? AND status='published' AND NOW() BETWEEN starts_at AND ends_at FOR UPDATE");$s->execute([$eid]);
    if(!$s->fetch()) throw new RuntimeException('This election is not currently open.');
    $s=$pdo->prepare('SELECT id FROM candidates WHERE id=? AND election_id=?');$s->execute([$cid,$eid]);
    if(!$s->fetch()) throw new RuntimeException('Invalid candidate selection.');
    $s=$pdo->prepare('INSERT INTO votes(election_id,candidate_id,voter_id) VALUES(?,?,?)');$s->execute([$eid,$cid,$uid]);
    $pdo->commit();flash('Your vote was recorded successfully.');
} catch(Throwable $ex) {
    if($pdo->inTransaction())$pdo->rollBack();
    flash($ex instanceof PDOException ? 'A vote has already been recorded for this election.' : $ex->getMessage(),'error');
}
header('Location: voter.php');exit;
