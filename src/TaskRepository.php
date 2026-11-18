<?php
declare(strict_types=1);

namespace Vox\SmartEngine;

use PDO;

final class TaskRepository
{
    private $db;

    public function __construct(PDO $db) { $this->db = $db; }

    public function insertPreview(int $campaignId, array $slots, array $payloads = []): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO vox_campaign_tasks
                (campaign_id, sequence_no, scheduled_at, task_type, payload_json, status, idempotency_key)
                VALUES (:campaign_id, :sequence_no, :scheduled_at, 'publish_content', :payload_json, 'preview', :key)"
            );
            foreach ($slots as $slot) {
                $payload = $payloads[$slot['sequence'] - 1] ?? [];
                $key = hash('sha256', $campaignId . ':' . $slot['sequence'] . ':' . $slot['scheduled_at']);
                $stmt->execute([
                    ':campaign_id' => $campaignId,
                    ':sequence_no' => $slot['sequence'],
                    ':scheduled_at' => $slot['scheduled_at'],
                    ':payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
                    ':key' => $key,
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function activateCampaign(int $campaignId): void
    {
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare("UPDATE vox_campaign_tasks SET status='queued' WHERE campaign_id=? AND status='preview'");
            $q->execute([$campaignId]);
            $q = $this->db->prepare("UPDATE vox_campaigns SET status='scheduled' WHERE id=? AND status='draft'");
            $q->execute([$campaignId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function pause(int $campaignId): void
    {
        $this->db->prepare("UPDATE vox_campaigns SET status='paused' WHERE id=? AND status IN ('draft','scheduled')")->execute([$campaignId]);
        $this->db->prepare("UPDATE vox_campaign_tasks SET status='cancelled' WHERE campaign_id=? AND status='queued'")->execute([$campaignId]);
    }
}
