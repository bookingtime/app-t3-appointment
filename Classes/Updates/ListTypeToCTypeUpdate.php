<?php

declare(strict_types=1);

namespace Bookingtime\Appointment\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Migriert bestehende tt_content-Eintraege des Plugins vom alten
 * list_type-Format (CType "list") zum CType "appointment_appointment".
 *
 * Bewusst ohne die Basisklasse AbstractListTypeToCTypeUpdate implementiert:
 * die existiert erst ab TYPO3 13.4, diese Extension muss aber auch auf
 * 12.4 lauffaehig sein.
 */
#[UpgradeWizard('btAppointmentListTypeToCType')]
final class ListTypeToCTypeUpdate implements UpgradeWizardInterface
{
    private const LIST_TYPE = 'appointment_appointment';

    public function getTitle(): string
    {
        return 'bookingtime appointment: Plugin von list_type zu CType migrieren';
    }

    public function getDescription(): string
    {
        return 'Stellt bestehende Inhaltselemente des bookingtime-appointment-Plugins'
            . ' vom entfernten list_type-Mechanismus auf das eigene CType'
            . ' "appointment_appointment" um.';
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter(self::LIST_TYPE))
            )
            ->set('CType', self::LIST_TYPE)
            ->set('list_type', '')
            ->executeStatement();
        return true;
    }

    public function updateNecessary(): bool
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        // In TYPO3 14 kann die Spalte list_type bereits per Schema-Update
        // entfernt worden sein - dann gibt es nichts mehr zu migrieren.
        $columns = $connection->createSchemaManager()->listTableColumns('tt_content');
        if (!isset($columns['list_type'])) {
            return false;
        }

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter(self::LIST_TYPE))
            )
            ->executeQuery()
            ->fetchOne();
        return ((int)$count) > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
