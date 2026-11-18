<?php
declare(strict_types=1);

namespace Vox\SmartEngine;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class CampaignPlanner
{
    /**
     * Plan a sequence of real content-publishing tasks.
     * This function never creates engagement quantities.
     */
    public function preview(array $input): array
    {
        $tzName = (string)($input['timezone'] ?? 'UTC');
        try { $tz = new DateTimeZone($tzName); }
        catch (\Throwable $e) { throw new InvalidArgumentException('Invalid timezone'); }

        $start = new DateTimeImmutable((string)($input['start_at'] ?? ''), $tz);
        $end = new DateTimeImmutable((string)($input['end_at'] ?? ''), $tz);
        $items = filter_var($input['items'] ?? null, FILTER_VALIDATE_INT);
        if (!$items || $items < 1 || $items > 200) {
            throw new InvalidArgumentException('items must be between 1 and 200');
        }
        if ($end <= $start) throw new InvalidArgumentException('end_at must be after start_at');

        $curve = (string)($input['curve'] ?? 's_curve');
        if (!in_array($curve, ['linear', 's_curve'], true)) {
            throw new InvalidArgumentException('Unsupported curve');
        }

        $quiet = $input['quiet_hours'] ?? ['start' => '22:00', 'end' => '07:00'];
        $peaks = $input['peak_windows'] ?? [];
        $slots = [];
        $duration = $end->getTimestamp() - $start->getTimestamp();
        $weights = [];

        for ($i = 0; $i < $items; $i++) {
            $p = ($i + 0.5) / $items;
            // Smoothstep S-curve; evenly spaced progress values, transformed
            // to produce gradual campaign pacing rather than engagement bursts.
            $fraction = $curve === 's_curve'
                ? ($p * $p * (3 - 2 * $p))
                : $p;
            $candidate = $start->modify('+' . (int)round($duration * $fraction) . ' seconds');
            $candidate = $this->moveOutOfQuietHours($candidate, $quiet, $tz);
            $weight = $this->peakWeight($candidate, $peaks);
            $slots[] = [
                'sequence' => $i + 1,
                'scheduled_at' => $candidate->format('Y-m-d H:i:s'),
                'timezone' => $tzName,
                'task_type' => 'publish_content',
                'audience_window_weight' => $weight,
                'status' => 'preview',
            ];
            $weights[] = $weight;
        }

        // Stable sort by scheduled time, preserving sequence for equal times.
        usort($slots, static fn($a, $b) => [$a['scheduled_at'], $a['sequence']] <=> [$b['scheduled_at'], $b['sequence']]);
        return [
            'platform' => (string)($input['platform'] ?? ''),
            'goal' => 'publish_content',
            'curve' => $curve,
            'total_tasks' => count($slots),
            'timezone' => $tzName,
            'slots' => $slots,
            'notice' => 'Preview only. Publishing requires user approval and an authorized platform adapter.',
        ];
    }

    private function moveOutOfQuietHours(DateTimeImmutable $dt, array $quiet, DateTimeZone $tz): DateTimeImmutable
    {
        $from = (string)($quiet['start'] ?? '22:00');
        $to = (string)($quiet['end'] ?? '07:00');
        if (!preg_match('/^\d{2}:\d{2}$/', $from) || !preg_match('/^\d{2}:\d{2}$/', $to)) {
            throw new InvalidArgumentException('Quiet hours must use HH:MM');
        }
        $time = $dt->format('H:i');
        $inside = $from > $to ? ($time >= $from || $time < $to) : ($time >= $from && $time < $to);
        if (!$inside) return $dt;

        [$h, $m] = array_map('intval', explode(':', $to));
        $target = $dt->setTime($h, $m, 0);
        if ($target <= $dt) $target = $target->modify('+1 day');
        return $target;
    }

    private function peakWeight(DateTimeImmutable $dt, array $windows): float
    {
        $time = $dt->format('H:i');
        foreach ($windows as $window) {
            $start = (string)($window['start'] ?? '');
            $end = (string)($window['end'] ?? '');
            $weight = (float)($window['weight'] ?? 1.0);
            if ($start <= $time && $time < $end) return max(1.0, min(3.0, $weight));
        }
        return 1.0;
    }
}
