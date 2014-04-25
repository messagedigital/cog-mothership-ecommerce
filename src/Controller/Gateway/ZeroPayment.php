<?php

namespace Message\Mothership\Ecommerce\Controller\Gateway;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Payable\PayableInterface;

class ZeroPayment extends Controller implements PurchaseControllerInterface, RefundControllerInterface
{
	const REFERENCE_PREFIX = "zero-payment-";

	/**
	 * {@inheritDoc}
	 */
	public function purchase(PayableInterface $payable, array $stages, array $options = null)
	{
		// Forward to the method for completing the payable and capture the
		// response containing the confirm url
		$successResponse = $this->forward($stages['success'], [
			'payable'   => $payable,
			'reference' => self::REFERENCE_PREFIX . $payable->getPayableTransactionID(),
			'method'    => $this->get('order.payment.methods')->get('manual'),
		]);

		$successData = (array) json_decode($successResponse->getContent());

		return $this->redirect($successData['url']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund(PayableInterface $payable, $reference, array $stages, array $options = null)
	{
		// Forward to the method for completing the payable and capture the
		// response containing the confirm url
		$successResponse = $this->forward($stages['success'], [
			'payable'   => $payable,
			'reference' => $reference,
			'method'    => $this->get('order.payment.methods')->get('manual'),
		]);

		$successData = (array) json_decode($successResponse->getContent());

		return $this->redirect($successData['url']);
	}
}