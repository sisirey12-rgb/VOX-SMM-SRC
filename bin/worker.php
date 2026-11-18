<?php
declare(strict_types=1);

/**
 * Queue worker entry point.
 * A real publisher adapter must be injected after OAuth scopes, account ownership,
 * platform policy checks, idempotency and rate-limit handling are implemented.
 * This starter intentionally refuses to publish without that adapter.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
fwrite(STDERR, "VOX worker: no official platform publisher adapter configured; no tasks executed.\n");
exit(2);
