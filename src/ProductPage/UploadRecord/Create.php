<?php

namespace Message\Mothership\Ecommerce\ProductPage\UploadRecord;

use Message\Cog\DB\Transaction;
use Message\Cog\DB\TransactionalInterface;

class Create implements TransactionalInterface
{
	/**
	 * @var Transaction
	 */
	private $_transaction;

	/**
	 * @var bool
	 */
	private $_transOverride = false;

	public function __construct(Transaction $transaction)
	{
		$this->_transaction = $transaction;
	}

	public function setTransaction(Transaction $transaction)
	{
		$this->_transaction   = $transaction;
		$this->_transOverride = true;
	}

	public function create(UploadRecord $record)
	{
		$this->_transaction->add("
			INSERT INTO
				product_page_upload_record
			VALUES
				(
					page_id      = :pageID?i,
					product_id   = :productID?i,
					unit_id      = :unitID?in,
					confirmed_at = :confirmedAt?dn,
					confirmed_by = :confirmedBy?in
				)
		", [
			'pageID'      => $record->getPageID(),
			'productID'   => $record->getProductID(),
			'unitID'      => $record->getUnitID(),
			'confirmedAt' => $record->getConfirmedAt(),
			'confirmedBy' => $record->getConfirmedBy(),
		]);

		if (!$this->_transOverride) {
			$this->_transaction->commit();
		}
	}
}