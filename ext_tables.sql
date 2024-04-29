#
# Table structure for table 'tx_appointment_domain_model_bookingtimepageurl'
#
CREATE TABLE tx_appointment_domain_model_bookingtimepageurl (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	disable tinyint(4) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
   url text DEFAULT '' NOT NULL,

	PRIMARY KEY (uid)
);
