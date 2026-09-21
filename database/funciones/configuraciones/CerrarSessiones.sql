SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE datname = 'hotel_bugambilias'
  AND pid <> pg_backend_pid();