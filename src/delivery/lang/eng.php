<?php
namespace delivery\lang;
interface eng {
	const DELIVERY_COMMAND = 'delivery';
	const DELIVERY_COMMAND_ADD = 'add';
	const DELIVERY_COMMAND_ADD_USAGE = '/' . self::DELIVERY_COMMAND . ' ' . self::DELIVERY_COMMAND_ADD . ' <amount>';
	const DELIVERY_COMMAND_ADD_DESCRIPTION = 'add the item on hand to box';
	const DELIVERY_COMMAND_RESET = 'reset';
	const DELIVERY_COMMAND_RESET_USAGE = '/' . self::DELIVERY_COMMAND . ' ' . self::DELIVERY_COMMAND_RESET;
	const DELIVERY_COMMAND_RESET_DESCRIPTION = 'make empty the box to send';
	const DELIVERY_COMMAND_OUTBOX = 'outbox';
	const DELIVERY_COMMAND_OUTBOX_USAGE = '/' . self::DELIVERY_COMMAND . ' ' . self::DELIVERY_COMMAND_OUTBOX;
	const DELIVERY_COMMAND_OUTBOX_DESCRIPTION = 'show item in box to send';
}