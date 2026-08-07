<?php


return [
    'ctrl' => [
        'title' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_db.xlf:bookingtimepageurl',
        'label' => 'title',
        'label_alt' => 'url',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:bt_appointment/Resources/Public/Icons/icon_bookingtimepageurl.png'
    ],
    'types' => [
        '1' => ['showitem' => 'title,url'],
    ],
    'columns' => [
        'title'=>[
            'label' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_db.xlf:bookingtimepageurl.title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'url'=>[
            'label' => 'LLL:EXT:bt_appointment/Resources/Private/Language/locallang_db.xlf:bookingtimepageurl.url',
            'config' => [
                'type' => 'input',
                'required'=>true,
                'placeholder' => 'https://module.bookingtime.com/booking/moduleConfig/5f8AaSVSFGrgSjv420Kbf69YRetxLIMj',
                'eval' => 'trim,unique',
                'size' => 100,
            ],
        ],
    ],
];
