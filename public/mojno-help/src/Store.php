<?php
// SQLite хранит независимые диалоги и атомарные счётчики запросов.
declare(strict_types=1);
final class Store {
    private PDO $db;
    public function __construct(string $path) {
        $this->db = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('PRAGMA busy_timeout=5000; PRAGMA journal_mode=WAL;
            CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, conversation TEXT, role TEXT, content TEXT, created INTEGER);
            CREATE INDEX IF NOT EXISTS history ON messages(conversation,id);
            CREATE TABLE IF NOT EXISTS limits (key TEXT PRIMARY KEY, hits INTEGER, expires INTEGER);');
    }
    public function history(string $id, int $limit): array {
        $q=$this->db->prepare('SELECT role,content FROM (SELECT id,role,content FROM messages WHERE conversation=? ORDER BY id DESC LIMIT ?) ORDER BY id');
        $q->bindValue(1,$id); $q->bindValue(2,$limit,PDO::PARAM_INT); $q->execute(); return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function pair(string $id, string $user, string $reply, int $max): void {
        $this->db->beginTransaction();
        try {
            $q=$this->db->prepare('INSERT INTO messages(conversation,role,content,created) VALUES(?,?,?,?)');
            $q->execute([$id,'user',$user,time()]); $q->execute([$id,'assistant',$reply,time()]);
            $q=$this->db->prepare('DELETE FROM messages WHERE conversation=? AND id NOT IN (SELECT id FROM messages WHERE conversation=? ORDER BY id DESC LIMIT ?)');
            $q->bindValue(1,$id);$q->bindValue(2,$id);$q->bindValue(3,$max,PDO::PARAM_INT);$q->execute();
            $this->db->commit();
        } catch(Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function clear(string $id): void { $this->db->prepare('DELETE FROM messages WHERE conversation=?')->execute([$id]); }
    public function allow(string $key, int $max): bool {
        $key .= ':' . intdiv(time(),60);
        $q=$this->db->prepare('INSERT INTO limits(key,hits,expires) VALUES(?,1,?) ON CONFLICT(key) DO UPDATE SET hits=hits+1 RETURNING hits');
        $q->execute([$key,time()+120]); return (int)$q->fetchColumn() <= $max;
    }
    public function prune(int $days): void {
        $this->db->prepare('DELETE FROM messages WHERE created < ?')->execute([time()-max(1,$days)*86400]);
        $this->db->prepare('DELETE FROM limits WHERE expires < ?')->execute([time()]);
    }
}
