<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Content
{
    public static function listing(string $kind, bool $published = false): array
    {
        ContentSchema::fields($kind); $db = Database::connect();
        $q = $db->prepare('SELECT * FROM ' . Database::table('content') . ' WHERE kind=? AND deleted_at IS NULL' . ($published ? ' AND published_json IS NOT NULL' : '') . ' ORDER BY sort_order,id DESC LIMIT 1000'); $q->execute([$kind]);
        return array_map(fn(array $row) => self::decode($row,$published),$q->fetchAll());
    }
    public static function get(int $id, bool $published = false): ?array
    {
        $q = Database::connect()->prepare('SELECT * FROM ' . Database::table('content') . ' WHERE id=? AND deleted_at IS NULL' . ($published ? ' AND published_json IS NOT NULL' : '')); $q->execute([$id]); $row = $q->fetch(); return $row ? self::decode($row,$published) : null;
    }
    public static function bySlug(string $kind, string $slug): ?array
    {
        ContentSchema::fields($kind);
        $q = Database::connect()->prepare('SELECT * FROM ' . Database::table('content') . ' WHERE kind=? AND published_slug=? AND published_json IS NOT NULL AND deleted_at IS NULL'); $q->execute([$kind,$slug]); $row = $q->fetch(); return $row ? self::decode($row,true) : null;
    }
    private static function decode(array $row, bool $published): array
    {
        $document = json_decode($published ? $row['published_json'] : $row['draft_json'],true,64,JSON_THROW_ON_ERROR);
        return ['id'=>(int)$row['id'],'kind'=>$row['kind'],'title'=>$document['title'],'slug'=>$document['slug'],'data'=>$document['data'],'revision'=>(int)$row['revision'],'sort_order'=>(int)$row['sort_order'],'is_published'=>$row['published_json'] !== null,'has_draft'=>$row['draft_json'] !== $row['published_json'],'published_at'=>$row['published_at'],'updated_at'=>$row['updated_at']];
    }
    public static function save(string $kind, array $input, int $actor, string $action): int
    {
        if (!in_array($action,['save','publish','unpublish'],true)) { throw new InvalidArgumentException('Unknown content action.'); }
        $id = max(0,(int)($input['id'] ?? 0)); $expected = (int)($input['revision'] ?? 0);
        $title = trim((string)($input['title'] ?? '')); if ($title === '' || strlen($title)>180 || str_contains($title,"\0")) { throw new InvalidArgumentException('A title of 1–180 bytes is required.'); }
        $slug = ContentSchema::slug(trim((string)($input['slug'] ?? '')),$title);
        $data = ContentSchema::validate($kind,$input,$action==='publish');
        $sort = filter_var($input['sort_order'] ?? 0,FILTER_VALIDATE_INT,['options'=>['min_range'=>-100000,'max_range'=>100000]]); if ($sort===false) { throw new InvalidArgumentException('Sort order must be a whole number between -100000 and 100000.'); }
        $document = json_encode(['title'=>$title,'slug'=>$slug,'data'=>$data],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $db = Database::connect(); $db->beginTransaction();
        try {
            $revision = 1;
            if ($id) {
                $q = $db->prepare('SELECT kind,revision FROM ' . Database::table('content') . ' WHERE id=? AND deleted_at IS NULL FOR UPDATE'); $q->execute([$id]); $old = $q->fetch();
                if (!$old || $old['kind'] !== $kind) { throw new InvalidArgumentException('This content item does not exist in the selected section.'); }
                if ((int)$old['revision'] !== $expected) { throw new RuntimeException('This item changed in another session. Reload and review the latest version before saving; your changes were not applied.'); }
                $revision = $expected+1;
            }
            $media = ContentSchema::mediaIds($kind,$data);
            foreach ($media as $mediaId=>$mediaKind) {
                $q = $db->prepare('SELECT kind FROM ' . Database::table('media') . ' WHERE id=?'); $q->execute([$mediaId]);
                if ($q->fetchColumn() !== $mediaKind) { throw new InvalidArgumentException('A selected media file is missing or has the wrong type.'); }
            }
            if ($kind==='track' && $data['release_id']) {
                $q=$db->prepare('SELECT id FROM '.Database::table('content')." WHERE id=? AND kind='release' AND deleted_at IS NULL");$q->execute([$data['release_id']]);
                if (!$q->fetchColumn()) { throw new InvalidArgumentException('Select an existing release for this track.'); }
            }
            if (!$id) {
                $q=$db->prepare('INSERT INTO '.Database::table('content').' (kind,title,draft_slug,draft_json,revision,sort_order,updated_by,created_at,updated_at) VALUES (?,?,?,?,1,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())'); $q->execute([$kind,$title,$slug,$document,$sort,$actor]);$id=(int)$db->lastInsertId();
            } else {
                $q=$db->prepare('UPDATE '.Database::table('content').' SET title=?,draft_slug=?,draft_json=?,revision=?,sort_order=?,updated_by=?,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$title,$slug,$document,$revision,$sort,$actor,$id]);
            }
            self::links($db,$id,'draft',$media);
            if ($action==='publish') {
                $q=$db->prepare('UPDATE '.Database::table('content').' SET published_slug=?,published_json=?,published_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$slug,$document,$id]); self::links($db,$id,'published',$media);
            } elseif ($action==='unpublish') {
                $q=$db->prepare('UPDATE '.Database::table('content').' SET published_slug=NULL,published_json=NULL,published_at=NULL WHERE id=?');$q->execute([$id]);self::links($db,$id,'published',[]);
            }
            $q=$db->prepare('INSERT INTO '.Database::table('content_revisions').' (content_id,revision,draft_json,action,actor_id,created_at) VALUES (?,?,?,?,?,UTC_TIMESTAMP())');$q->execute([$id,$revision,$document,$action,$actor]);
            Database::audit($db,$actor,'content.'.$kind.'.'.$action);$db->commit();return $id;
        } catch (Throwable $e) {
            if ($db->inTransaction()) { $db->rollBack(); }
            if ($e instanceof PDOException && (string)$e->getCode()==='23000') { throw new InvalidArgumentException('That URL name is already in use in this section. Choose a different URL name.'); }
            throw $e;
        }
    }
    private static function links(PDO $db,int $id,string $visibility,array $media): void
    {
        $q=$db->prepare('DELETE FROM '.Database::table('content_media').' WHERE content_id=? AND visibility=?');$q->execute([$id,$visibility]);
        $q=$db->prepare('INSERT INTO '.Database::table('content_media').' (content_id,media_id,visibility) VALUES (?,?,?)');
        foreach (array_keys($media) as $mediaId) {$q->execute([$id,$mediaId,$visibility]);}
    }
    public static function publicMedia(int $id): bool
    {
        $q=Database::connect()->prepare('SELECT COUNT(*) FROM '.Database::table('content_media').' cm JOIN '.Database::table('content')." c ON c.id=cm.content_id WHERE cm.media_id=? AND cm.visibility='published' AND c.published_json IS NOT NULL AND c.deleted_at IS NULL");$q->execute([$id]);return (int)$q->fetchColumn()>0;
    }
    public static function tracks(int $releaseId): array { return array_values(array_filter(self::listing('track',true),fn(array $t)=>(int)$t['data']['release_id']===$releaseId)); }
    public static function events(bool $past=false,string $city=''): array
    {
        $now=gmdate('Y-m-d H:i:s');$items=array_filter(self::listing('event',true),function(array $e)use($past,$city,$now):bool{$d=$e['data'];return !empty($d['starts_utc']) && (($d['starts_utc']<$now)===$past) && ($city==='' || $d['city']===$city);});
        usort($items,fn(array $a,array $b)=>$past ? strcmp($b['data']['starts_utc'],$a['data']['starts_utc']) : strcmp($a['data']['starts_utc'],$b['data']['starts_utc']));return array_values($items);
    }
}
