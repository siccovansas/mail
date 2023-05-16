<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 Daniel Kesselberg <mail@danielkesselberg.de>
 *
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Mail\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version3002Date20230515125224 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$tableName = 'mail__certificates';
		if (!$schema->hasTable($tableName)) {
			$table = $schema->createTable($tableName);
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 20,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('email_address', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('certificate', Types::TEXT, [
				'notnull' => true,
			]);
			$table->addColumn('private_key', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addIndex(['user_id'], 'mail_smime_certs_uid_idx');
			$table->addIndex(['id', 'user_id'], 'mail_smime_certs_id_uid_idx');
		}

		return $schema;

		$messagesTable = $schema->getTable('mail_messages');
		if (!$messagesTable->hasColumn('dkim_status')) {
			$messagesTable->addColumn('dkim_status', Types::SMALLINT, [
				'notnull' => true,
				'default' => 0,
			]);
		}
		if (!$messagesTable->hasColumn('dkim_reason')) {
			$messagesTable->addColumn('dkim_reason', Types::STRING, [
				'notnull' => true,
			]);
		}

		return $schema;
	}
}
