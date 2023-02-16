<?php
namespace DoctrineMigrations;

use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230215120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add news and users tables';
    }

    public function up(Schema $schema): void
{
    // Create the news table
    $newsTable = $schema->createTable('news');
    $newsTable->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
    $newsTable->addColumn('title', Types::STRING);
    $newsTable->addColumn('description', Types::TEXT);
    $newsTable->addColumn('picture', Types::STRING);
    $newsTable->addColumn('date', Types::DATETIME_MUTABLE);
    $newsTable->addColumn('updated_at', Types::DATETIME_MUTABLE);
    $newsTable->setPrimaryKey(['id']);

    // Create the users table
    $usersTable = $schema->createTable('users');
    $usersTable->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
    $usersTable->addColumn('email', Types::STRING);
    $usersTable->addColumn('password', Types::STRING);
    $usersTable->addColumn('roles', Types::JSON);
    $usersTable->setPrimaryKey(['id']);
}


    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP TABLE users');
    }
}
