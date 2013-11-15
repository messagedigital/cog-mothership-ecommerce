<?php

namespace Message\Mothership\Ecommerce\Bootstrap;

use Message\Mothership\Ecommerce\OrderItemStatuses;

use Message\Mothership\Commerce\Order\Status\Status;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$this->addOrderStatuses($services);
		$this->registerEmails($services);

		$services['form.orders.checkbox'] = function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Orders($sm);
		};

		$services['form.pickup'] = function($sm) {
			return new \Message\Mothership\Ecommerce\Form\Pickup($sm);
		};

		$services['file.packing_slip'] = function($sm) {
			return new \Message\Mothership\Ecommerce\File\PackingSlip($sm);
		};

		$services['ecom.file.loader'] = function($sm) {
			return new \Message\Mothership\Ecommerce\File\Loader(
				$sm['db.query']
			);
		};

		$services['checkout.hash'] = $services->share(function($c) {
			return new \Message\Cog\Security\Hash\SHA1($c['security.salt']);
		});
	}

	public function addOrderStatuses($services)
	{
		$services['order.statuses']
			->add(new Status(OrderItemStatuses::RETURNED, 'Fully returned'));

		$services['order.item.statuses']
			->add(new Status(OrderItemStatuses::AWAITING_PAYMENT, 'Awaiting Payment'))
			->add(new Status(OrderItemStatuses::HOLD,             'On Hold'))
			->add(new Status(OrderItemStatuses::PRINTED,          'Printed'))
			->add(new Status(OrderItemStatuses::PICKED,           'Picked'))
			->add(new Status(OrderItemStatuses::PACKED,           'Packed'))
			->add(new Status(OrderItemStatuses::POSTAGED,         'Postaged'))
			->add(new Status(OrderItemStatuses::RETURN_WAITING,   'Waiting to Receive Returned Item'))
			->add(new Status(OrderItemStatuses::RETURN_ARRIVED,   'Returned Item Arrived'))
			->add(new Status(OrderItemStatuses::RETURNED,         'Returned'));
	}

	public function registerEmails($services)
	{
		$services['mail.factory.order.confirmation'] = function($c) {
			$factory = new \Message\Cog\Mail\Factory($c['mail.message']);

			$factory->requires('order', 'payments');

			$factory->extend(function($factory, $message) {
				$message->setTo($factory->order->user->email);
				$message->setSubject(sprintf('Your %s order confirmation - %d', $c['cfg']->app->name, $factory->order->orderID));
				$message->setView('Message:Mothership:Ecommerce::mail:order:confirmation', array(
					'order'       => $factory->order,
					'payments'    => $factory->payments,
					'companyName' => $c['cfg']->app->name,
				));
			});

			return $factory;
		};
	}
}