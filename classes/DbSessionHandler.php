<?php
/**
 * Database-backed session storage.
 *
 * PHP's default session handler writes files to local disk. That is fine on a
 * single server, but on any host that runs more than one instance — serverless
 * platforms included — each request can land on a different machine with a
 * different disk, so a session written by one request is invisible to the next.
 * Storing sessions in MySQL makes them shared and durable.
 *
 * Enabled by setting SESSION_DRIVER=database (see config/config.php). Local
 * XAMPP keeps using files unless that is set, so nothing changes in dev.
 */
class DbSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private ?Database $db = null;
    private int $ttl;

    public function __construct(?int $ttl = null)
    {
        // Deliberately no connection here. session_set_save_handler() runs during
        // bootstrap on every request, so connecting in the constructor would make
        // a database outage fatal at config load instead of at first session use.
        $this->ttl = $ttl ?? (int) ini_get('session.gc_maxlifetime') ?: 1440;
    }

    /** Connect on first actual use, then reuse. */
    private function db(): Database
    {
        return $this->db ??= Database::getInstance();
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $row = $this->db()->fetchOne(
            "SELECT payload FROM sessions WHERE id = :id AND expires_at > NOW() LIMIT 1",
            [':id' => $id]
        );
        return $row['payload'] ?? '';
    }

    public function write(string $id, string $data): bool
    {
        // MySQL UPSERT keeps this a single round trip.
        $this->db()->query(
            "INSERT INTO sessions (id, payload, expires_at)
             VALUES (:id, :payload, DATE_ADD(NOW(), INTERVAL :ttl SECOND))
             ON DUPLICATE KEY UPDATE
                payload    = VALUES(payload),
                expires_at = VALUES(expires_at)",
            [':id' => $id, ':payload' => $data, ':ttl' => $this->ttl]
        );
        return true;
    }

    public function destroy(string $id): bool
    {
        $this->db()->query("DELETE FROM sessions WHERE id = :id", [':id' => $id]);
        return true;
    }

    /** @return int|false */
    #[\ReturnTypeWillChange]
    public function gc(int $max_lifetime)
    {
        $this->db()->query("DELETE FROM sessions WHERE expires_at < NOW()");
        return true;
    }

    /**
     * Called when a session is read but unchanged. Refreshing only the expiry
     * avoids rewriting the whole payload on every request.
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        $this->db()->query(
            "UPDATE sessions SET expires_at = DATE_ADD(NOW(), INTERVAL :ttl SECOND) WHERE id = :id",
            [':id' => $id, ':ttl' => $this->ttl]
        );
        return true;
    }

    public function validateId(string $id): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9,\-]{22,256}$/', $id);
    }
}
