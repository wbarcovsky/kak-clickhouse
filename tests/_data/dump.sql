CREATE TABLE IF NOT EXISTS `test_stat` (
    `event_date` Date,
    `time` Int32,
    `user_id` UInt32,
    `counter_id` Nullable (UInt32),
    `ip` Int64,
    `click` UInt8,
    `is_unique` UInt8,
    `click_id` String
) ENGINE = MergeTree(event_date, (user_id), 8192);