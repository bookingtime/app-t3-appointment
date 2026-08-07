#
# Table structure for table 'tx_appointment_domain_model_bookingtimepageurl'
#
# Verwaltungsspalten (uid, pid, tstamp, crdate, deleted, hidden, starttime,
# endtime, sorting, Sprach- und Workspace-Felder) erzeugt TYPO3 seit v12
# automatisch aus dem TCA-ctrl.
#
CREATE TABLE tx_appointment_domain_model_bookingtimepageurl (
	title varchar(255) DEFAULT '' NOT NULL,
	url text
);
