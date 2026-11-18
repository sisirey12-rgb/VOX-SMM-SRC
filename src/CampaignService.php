<?php
declare(strict_types=1);

namespace Vox\SmartEngine;

use PDO;

final class CampaignService
{
    private $db;

    public function __construct(PDO $db) { $this->db = $db; }

    public function create(int $userId, string $name, string $platform, string $timezone, string $startsAt, string $endsAt, array $settings): int
    {
        $allowed = ['instagram','facebook','youtube','tiktok','telegram'];
        if (!in_array($platform, $allowed, true)) throw new \InvalidArgumentException('Unsupported platform');
        $stmt = $this->db->prepare(
            "INSERT INTO vox_campaigns (user_id,name,platform,goal,timezone,status,starts_at,ends_at,settings_json)
             VALUES (:uid,:name,:platform,'publish_content',:tz,'draft',:start,:end,:settings)"
        );
        $stmt->execute([
            ':uid'=>$userId, ':name'=>$name, ':platform'=>$platform, ':tz'=>$timezone,
            ':start'=>$startsAt, ':end'=>$endsAt, ':settings'=>json_encode($settings, JSON_THROW_ON_ERROR),
        ]);
        return (int)$this->db->lastInsertId();
    }
}
