-- Run once against the configured panel database before enabling Google sign-in.
ALTER TABLE `general_users`
  ADD COLUMN `google_sub` varchar(191) DEFAULT NULL AFTER `login_type`,
  ADD UNIQUE KEY `uq_general_users_google_sub` (`google_sub`);

-- VOX defaults. Existing installations may already have customized these options.
UPDATE `general_options` SET `value` = 'VOX SMM' WHERE `name` = 'website_name';
UPDATE `general_options` SET `value` = 'VOX SMM | SOCIAL GROWTH. REDEFINED.' WHERE `name` = 'website_title';
UPDATE `general_options` SET `value` = 'Social campaign management, analytics, and support. SOCIAL GROWTH. REDEFINED.' WHERE `name` = 'website_desc';
UPDATE `general_options` SET `value` = 'Copyright &copy; VOX SMM' WHERE `name` = 'copy_right_content';
