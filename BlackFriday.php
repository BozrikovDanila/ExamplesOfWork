<?php

namespace App\Braind\Main;

use App\Braind\Main\Tools\Images;

class BlackFriday
{
	/**
	 * Префикс страниц черной пятницы
	 */
	public const string BLACK_FRIDAY_PREFIX = 'black-friday';

	/**
	 * Активность Чёрной Пятницы
	 */
	private const string BLACK_FRIDAY_ACTIVE_CODE = 'BLACK_FRIDAY_ACTIVATED';

	/**
	 * Период активности Чёрной Пятницы
	 */
	private const string PERIOD_CODE = 'BF_PERIOD';

	/**
	 * Показывать ли баннер вверху страницы
	 */
	private const string TOP_BANNER_ACTIVE_CODE = 'TOP_BANNER_ACTIVATED';

	/**
	 * Ссылка или id формы, по нажатию на баннер вверху страницы
	 */
	private const string TOP_BANNER_URL_CODE = 'TOP_BANNER_URL';

	/**
	 * Текст на баннере вверху страницы
	 */
	private const string TOP_BANNER_TEXT_CODE = 'TOP_BANNER_TEXT';

	/**
	 * Показывать ли плавающий баннер
	 */
	private const string FLOAT_BANNER_ACTIVE_CODE = 'FLOAT_BANNER_ACTIVATED';

	/**
	 * Изображение плавающего баннера
	 */
	private const string FLOAT_BANNER_IMAGE_CODE = 'FLOAT_BANNER_IMAGE';

	/**
	 * Ссылка или id формы, по нажатию на плавающий баннер
	 */
	private const string FLOAT_BANNER_URL_CODE = 'FLOAT_BANNER_URL';

	/**
	 * Показывать ли кнопку "Оставить заявку" на главной странице
	 */
	private const string MAIN_PAGE_BTN_ACTIVE_CODE = 'MAIN_PAGE_BTN_ACTIVATED';

	/**
	 * Показывать ли баннер на странице основного магазина карт
	 */
	private const string CARDS_SHOP_BANNER_ACTIVE_CODE = 'CARDS_SHOP_BANNER_ACTIVATED';

	/**
	 * Изображение баннера на странице основного магазина карт
	 */
	private const string CARDS_SHOP_BANNER_IMAGE_CODE = 'CARDS_SHOP_BANNER_IMAGE';


	/**
	 * Настройки периода активности ЧП
	 * ##### Проверяет, входит ли текущая дата и время в заданный интервал времени
	 *
	 * @return bool
	 * @throws \DateMalformedStringException
	 */
	public static function isAvailable(): bool
	{
		if (!self::isActive()) {
			return false;
		}

		if ($blackFridayPeriod = Settings::get(self::PERIOD_CODE)) {
			$curDate = new \DateTime();
			['date_from' => $startDate, 'date_to' => $endDate] = unserialize($blackFridayPeriod, ['allowed_classes' => false]);

			if ($startDate) {
				$startDate = new \DateTime($startDate);
				$result = $curDate >= $startDate;
			}

			if ($endDate) {
				$endDate = new \DateTime($endDate);
				$result = ($result ?? true) && ($curDate <= $endDate);
			}

			return $result ?? false;
		}

		return false;
	}

	public static function isActive(): bool
	{
		return Settings::get(self::BLACK_FRIDAY_ACTIVE_CODE) === 'Y';
	}

	/**
	 * Проверка является ли страница ЧП или имеет GET параметр ЧП
	 *
	 * @param string $page
	 * @return bool
	 */
	public static function isBlackFridayPage(string $page): bool
	{
		return self::pageWithBlackFridayPrefix($page)
			|| isset($_GET[self::BLACK_FRIDAY_PREFIX]);
	}

	/**
	 * Имеет ли страница префикс ЧП
	 *
	 * @param string $page
	 * @return bool
	 */
	public static function pageWithBlackFridayPrefix(string $page): bool
	{
		return str_starts_with($page, "/".self::BLACK_FRIDAY_PREFIX);
	}

	/**
	 * Настройки верхнего баннера
	 * ##### Возвращает активен ли баннер
	 *
	 * @return bool
	 */
	public static function topBannerIsActive(): bool
	{
		return Settings::get(self::TOP_BANNER_ACTIVE_CODE) === 'Y';
	}

	/**
	 * Настройки верхнего баннера
	 * ##### Возвращает ссылку или id формы баннера
	 *
	 * @return string
	 */
	public static function topBannerUrlOrFormId(): string
	{
		return Settings::get(self::TOP_BANNER_URL_CODE);
	}

	/**
	 * Настройки верхнего баннера
	 * ##### Возвращает текст баннера
	 *
	 * @return string
	 */
	public static function topBannerText(): string
	{
		return Settings::get(self::TOP_BANNER_TEXT_CODE);
	}

	/**
	 * Настройки баннера на странице основного магазина карт
	 * ##### Возвращает активен ли баннер
	 *
	 * @return string
	 */
	public static function isCardsShopBannerActive(): string
	{
		return Settings::get(self::CARDS_SHOP_BANNER_ACTIVE_CODE) === 'Y';
	}

	/**
	 * Настройки баннера на странице основного магазина карт
	 * ##### Возвращает изображения баннера
	 *
	 * @return array в формате ['x1','x2']
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function cardsShopBannerImages(): array
	{
		return Images::makeImage(
			Settings::get(self::CARDS_SHOP_BANNER_IMAGE_CODE),
			'cards_shop_bf_banner',
		);
	}

	/**
	 * Настройки плавающего баннера
	 * ##### Возвращает изображения баннера
	 *
	 * @return array в формате ['x1','x2']
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function floatBannerImages(): array
	{
		return Images::makeImage(
			Settings::get(self::FLOAT_BANNER_IMAGE_CODE),
			'float_banner',
			[],
			[
				'crop_before' => true,
				'crop_where'  => 'center',
			]
		);
	}

	/**
	 * Настройки плавающего баннера
	 * ##### Возвращает активен ли баннер
	 *
	 * @return bool
	 */
	public static function isFloatBannerActive(): bool
	{
		return Settings::get(self::FLOAT_BANNER_ACTIVE_CODE) === 'Y';
	}

	/**
	 * Настройки плавающего баннера
	 * ##### Возвращает ссылку или id формы баннера
	 *
	 * @return string
	 */
	public static function floatBannerUrlOrFormId(){
		return Settings::get(self::FLOAT_BANNER_URL_CODE);
	}

	/**
	 * Настройки кнопки "Оставить заявку" на главной странице
	 * #### Возвращает активна ли кнопка
	 *
	 * @return bool
	 */
	public static function isMainPageBtnActive(): bool
	{
		return Settings::get(self::MAIN_PAGE_BTN_ACTIVE_CODE) === 'Y';
	}
}
