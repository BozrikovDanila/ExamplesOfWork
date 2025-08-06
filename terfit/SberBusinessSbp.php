<?php

namespace App\Braind\Main\Catalog\Payment\Handler;

use App\Braind\Main\ExternalService\Payment;
use Bitrix\Main\Web\Uri;

/**
 * Использует шлюзы СберБизнеса
 * @link https://ecomtest.sberbank.ru/doc Документация
 */
class SberBusinessSbp extends BaseSberBusiness
{
	public const string QR_TYPE = 'DYNAMIC_QR_SBP';
	public const string SBP_SCENARIO = 'C2B';

	public const PROP_QRC_ID = "SBP_QRC_ID";
	public const PROP_QRC_PAYLOAD = "SBP_QRC_PAYLOAD";

	/**
	 * @return string
	 */
	public static function getCode(): string
	{
		return "SBERBUSINESS_SBP";
	}

	protected function getGateMode(): Payment\SberBusinessMode
	{
		return Payment\SberBusinessMode::SBP;
	}

	/**
	 * Форматируем результат для переадресации оплаты по QR в НСКП
	 * @param array $result
	 * @return array
	 */
	protected function formatSuccessInitPay(array $result, array $arParams = []): array
	{
		$redirectUri = new Uri($arParams["QR_URL"]);
		$redirectUri->addParams([
			"orderNumber" => $this->payment->getOrder()->getField("ACCOUNT_NUMBER")
		]);

		return [
			self::RESULT_REDIRECT    => $redirectUri->getUri(),
			self::RESULT_ORDER_PROPS => [
				self::PROP_PAY_ORDER_ID		=> $result['orderId'],
				self::PROP_QRC_ID			=> $result["externalParams"]["qrcId"],
				self::PROP_QRC_PAYLOAD		=> $result['externalParams']['sbpPayload']
			]
		];
	}
}


