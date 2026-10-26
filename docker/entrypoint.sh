#!/bin/sh
# CONTAINER_ROLE=web (default): Apache, plus the email worker in the background
#                                unless EMAIL_WORKER=off.
# CONTAINER_ROLE=worker:        only the email worker (for a separate container).
set -e

run_email_worker() {
    while true; do
        php /var/www/html/mails/workers/send_registration_emails.php || true
        sleep "${EMAIL_WORKER_INTERVAL:-30}"
    done
}

if [ "${CONTAINER_ROLE:-web}" = "worker" ]; then
    run_email_worker
fi

if [ "${EMAIL_WORKER:-on}" != "off" ]; then
    run_email_worker &
fi

exec apache2-foreground
