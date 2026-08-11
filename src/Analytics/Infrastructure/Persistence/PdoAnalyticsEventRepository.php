<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Infrastructure\Persistence;

use ClubinhoPet\Analytics\Domain\AnalyticsEvent;
use ClubinhoPet\Analytics\Domain\AnalyticsEventRepository;
use PDO;

final class PdoAnalyticsEventRepository implements AnalyticsEventRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function insertMany(array $events): int
    {
        if ($events === []) {
            return 0;
        }

        $sql = 'INSERT INTO analytics_events
            (visitor_id, session_id, event_name, path, plan_slug, email, properties,
             referrer, utm_source, utm_medium, utm_campaign, ip_hash, user_agent)
            VALUES
            (:visitor_id, :session_id, :event_name, :path, :plan_slug, :email, :properties,
             :referrer, :utm_source, :utm_medium, :utm_campaign, :ip_hash, :user_agent)';
        $stmt = $this->pdo->prepare($sql);
        $n = 0;
        foreach ($events as $event) {
            $props = $event->properties();
            $stmt->execute([
                'visitor_id' => $event->visitorId(),
                'session_id' => $event->sessionId(),
                'event_name' => $event->eventName(),
                'path' => $event->path(),
                'plan_slug' => $event->planSlug(),
                'email' => $event->email(),
                'properties' => $props === null ? null : json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'referrer' => $event->referrer(),
                'utm_source' => $event->utmSource(),
                'utm_medium' => $event->utmMedium(),
                'utm_campaign' => $event->utmCampaign(),
                'ip_hash' => $event->ipHash(),
                'user_agent' => $event->userAgent(),
            ]);
            $n++;
        }

        return $n;
    }

    public function funnelSince(\DateTimeInterface $since): array
    {
        $names = [
            'page_view',
            'quiz_start',
            'quiz_complete',
            'checkout_click',
            'subscribe_submit',
            'lead_submit',
            'contact_submit',
        ];
        $counts = array_fill_keys($names, 0);

        $stmt = $this->pdo->prepare(
            'SELECT event_name, COUNT(*) AS c
             FROM analytics_events
             WHERE created_at >= :since AND event_name IN (
                \'page_view\',\'quiz_start\',\'quiz_complete\',\'checkout_click\',
                \'subscribe_submit\',\'lead_submit\',\'contact_submit\'
             )
             GROUP BY event_name'
        );
        $stmt->execute(['since' => $since->format('Y-m-d H:i:s')]);
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $counts[(string) $row['event_name']] = (int) $row['c'];
        }

        $uv = $this->pdo->prepare(
            'SELECT COUNT(DISTINCT visitor_id) FROM analytics_events
             WHERE created_at >= :since AND event_name = \'page_view\''
        );
        $uv->execute(['since' => $since->format('Y-m-d H:i:s')]);

        return [
            'counts' => $counts,
            'unique_visitors' => (int) $uv->fetchColumn(),
        ];
    }

    public function topPathsSince(\DateTimeInterface $since, int $limit = 8): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(path, \'/\') AS path, COUNT(*) AS views
             FROM analytics_events
             WHERE created_at >= :since AND event_name = \'page_view\'
             GROUP BY path
             ORDER BY views DESC
             LIMIT :lim'
        );
        $stmt->bindValue('since', $since->format('Y-m-d H:i:s'));
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $out = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $out[] = ['path' => (string) $row['path'], 'views' => (int) $row['views']];
        }

        return $out;
    }

    public function checkoutByPlanSince(\DateTimeInterface $since): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(plan_slug, \'(sem plano)\') AS plan_slug, COUNT(*) AS clicks
             FROM analytics_events
             WHERE created_at >= :since AND event_name = \'checkout_click\'
             GROUP BY plan_slug
             ORDER BY clicks DESC'
        );
        $stmt->execute(['since' => $since->format('Y-m-d H:i:s')]);
        $out = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $out[] = ['plan_slug' => (string) $row['plan_slug'], 'clicks' => (int) $row['clicks']];
        }

        return $out;
    }

    public function countEventSince(string $eventName, \DateTimeInterface $since): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM analytics_events WHERE event_name = :n AND created_at >= :since'
        );
        $stmt->execute([
            'n' => $eventName,
            'since' => $since->format('Y-m-d H:i:s'),
        ]);

        return (int) $stmt->fetchColumn();
    }
}
