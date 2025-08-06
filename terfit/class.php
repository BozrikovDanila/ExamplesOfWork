<?php

use App\Braind\Main\Data\Clubs;
use App\Braind\Main\Pages\ExistChecker;
use App\Braind\Main\Pages\PageListElement;
use App\Braind\Main\Tools\Cache;
use App\Braind\Main\Utm\UtmSaver;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\Elements\ElementNewsTable;
use Bitrix\Iblock\Elements\ElementServicesTable;
use Bitrix\Iblock\Elements\ElementTrainersTable;
use Bitrix\Iblock\Elements\ElementVacanciesTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

class Pages extends CBitrixComponent
{
	/**
	 * @var array<string, PageListElement>
	 */
	public static array $DEPEND_PAGES_LIST_NEW;

	//Детальные страницы, услуг и тренеров
	public const DIFFERENT_PAGES
		= [
			'services/list' => ['template' => 'service'],
			'trainers/detail' => ['template' => 'trainer'],
			'vacancies/detail' => ['template' => 'vacancy'],
			'life/detail' => ['template' => 'news.item']
		];

	public const ELEM_CLASS =
		[
			'life' => ElementNewsTable::class,
			'services' => ElementServicesTable::class,
			'trainers' => ElementTrainersTable::class,
			'vacancies' => ElementVacanciesTable::class,
		];


	public function __construct($component = null)
	{
		parent::__construct($component);

		if (!isset(self::$DEPEND_PAGES_LIST_NEW)) {
			self::setDependPagesList();
		}
	}

	/**
	 * Устанавливаем страницы
	 * @return void
	 */
	protected static function setDependPagesList(): void
	{
		self::$DEPEND_PAGES_LIST_NEW = [
			'clubs' => new PageListElement(
				'clubs',
				'clubs.list',
				true,
				true,
				null,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_BANNERS,
					IBLOCK_ID_ZONE_SLIDER, IBLOCK_ID_CLUBS_WILL_BE_INTERESTED,
					IBLOCK_ID_CLUBS_TRENDS, IBLOCK_ID_CLUBS_FAMILY_FITNESS,
					IBLOCK_ID_TRAINERS, IBLOCK_ID_CLUBS_ACTION,
				]
			),
			't' => new PageListElement(
				'clubs',
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_CLUBS_ACTION,
					IBLOCK_ID_ZONE_SLIDER, IBLOCK_ID_CLUBS_WILL_BE_INTERESTED,
					IBLOCK_ID_CLUBS_TRENDS, IBLOCK_ID_CLUBS_FAMILY_FITNESS,
					IBLOCK_ID_TRAINERS,
				]
			),
			't-swim-school' => new PageListElement(
				'swim.school',
				'swim.school',
				cached: true,
				existCheck: [ExistChecker::class, 'swimSchool'],
				iblockDependence: [
					IBLOCK_ID_CLUBS_ACTION,
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_SWIM,
					IBLOCK_ID_BANNERS, IBLOCK_ID_SWIM,
					IBLOCK_ID_TRAINERS, IBLOCK_ID_CLUBS_SLIDER
				]
			),
			't-teens' => new PageListElement(
				'teens',
				'teens',
				cached: true,
				existCheck: [ExistChecker::class, 'landing'],
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_TEEN,
					IBLOCK_ID_CLUBS_SLIDER, IBLOCK_ID_TRAINERS,
					IBLOCK_ID_CLUBS_ACTION
				]
			),
			't-aquacomplex' => new PageListElement(
				'aquacomplex',
				'clubs.list',
				cached: true,
				existCheck: [ExistChecker::class, 'aquacomplex'],
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_AQUACOMPLEX,
					IBLOCK_ID_CLUBS_SLIDER, IBLOCK_ID_TRAINERS,
				]
			),
			'cards' => new PageListElement(
				'cards',
				'cards',
				stopRedirect: true,
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS,
					IBLOCK_ID_CLUBS_CARDS, IBLOCK_ID_PAGE_CARDS,
					IBLOCK_ID_CATALOG_CLUB, IBLOCK_ID_CATALOG_CARD
				]
			),
			'schedule' => new PageListElement('schedule', 'schedule'),
			'contacts' => new PageListElement(
				'contacts.chosen',
				'contacts.not.chosen',
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS
				]
			),
			'services' => new PageListElement('services.list', 'services.list'),
			'trainers' => new PageListElement('trainers.list', 'trainers.list'),
			'vacancies' => new PageListElement('vacancies', 'vacancies'),
			'teens' => new PageListElement(
				'teens',
				'teens',
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_TEEN,
					IBLOCK_ID_CLUBS_SLIDER, IBLOCK_ID_TRAINERS,
					IBLOCK_ID_CLUBS_ACTION
				]
			),
			'spa' => new PageListElement(
				'spa',
				'spa',
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_SPA,
					IBLOCK_ID_CLUBS_SLIDER, IBLOCK_ID_TRAINERS,
					IBLOCK_ID_SPA
				]
			),
			'kids' => new PageListElement(
				'kids',
				'kids',
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_KIDS,
					IBLOCK_ID_ZONE_SLIDER, IBLOCK_ID_CLUBS_SLIDER,
					IBLOCK_ID_CLUBS_ACTION,
				]
			),
			'about' => new PageListElement(
				unsel: 'about',
				stopRedirect: true,
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_ABOUT,
					IBLOCK_ID_CLUBS_OUR_HISTORY
				]
			),
			'career' => new PageListElement('career', 'career'),
			'rent' => new PageListElement(unsel: 'rent'),
			'life' => new PageListElement('news.list', 'news.list'),
			'spa-services' => new PageListElement(
				'spa-services',
				'spa-services',
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_SPA,
					IBLOCK_ID_SPA
				]
			),
			'checkout' => new PageListElement(unsel: 'checkout', stopRedirect: true),
			'swim-school' => new PageListElement(
				'swim.school',
				'swim.school',
				cached: true,
				existCheck: [ExistChecker::class, 'swimSchool'],
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_SWIM,
					IBLOCK_ID_BANNERS, IBLOCK_ID_SWIM,
					IBLOCK_ID_TRAINERS, IBLOCK_ID_CLUBS_SLIDER
				]
			),
			'tftarget' => new PageListElement(
				unsel: 'tftarget',
				cached: true,
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_CONTENT_SWIM,
					IBLOCK_ID_BANNERS, IBLOCK_ID_SWIM,
					IBLOCK_ID_TRAINERS, IBLOCK_ID_CLUBS_SLIDER
				]
			),
			'eda' => new PageListElement('eda'),
			'vouchers' => new PageListElement('voucher', 'voucher'),
			'aquacomplex' => new PageListElement(
				'aquacomplex',
				'clubs.list',
				cached: true,
				existCheck: [ExistChecker::class, 'aquacomplex'],
				iblockDependence: [
					IBLOCK_ID_CLUBS, IBLOCK_ID_PAGE_AQUACOMPLEX,
					IBLOCK_ID_CLUBS_SLIDER, IBLOCK_ID_TRAINERS,
				]
			),
		];
	}

	/**
	 * Возврат списка страниц
	 * @return array
	 */
	public static function getPagesList(): array {
		$arPagesList = [];
		if ($cardsShops = \App\Braind\Main\Data\ShopsPages::getAllActiveShopsPagesList(true)) {
			foreach ($cardsShops as $link => $cardsShop)
			{
				$link = str_replace("/", "", $link);
				if (!empty($link) && !isset($arPagesList[$link])) {
					$arPagesList[$link] = new PageListElement(
						!empty($cardsShop['template']) ? $cardsShop['template'] : 'additional.catalogs',
						!empty($cardsShop['template']) ? $cardsShop['template'] : 'additional.catalogs',
						false,
						false,
						!empty($cardsShop['existCheck']) ?
							[ExistChecker::class, $cardsShop['existCheck']] : null,
						iblockDependence: []
					);
				}
			}
		}

		return self::$DEPEND_PAGES_LIST_NEW + $arPagesList;
	}

	/**
	 * @param array<string, PageListElement> $arPageList
	 * @return array
	 */
	private static function getComponentByUrl(array $arPageList) : array
	{
		global $APPLICATION;

		$url = array_values(array_filter(explode('/', $APPLICATION->GetCurPage())));

		switch (count($url)) {
			//Общая страница
			case 1:
				//Редирект в случае если кука есть и нет параметра отключения редиректа
				if (!$arPageList[$url[0]]->stopRedirect && !empty($arPageList[$url[0]]->sel)
					&& ($club = Clubs::getCurClub())
				) {
					/**
					 * @TODO Возможно переделать на управлямую логику
					 *
					 * Редирект для страницы расписания для клуба Братиславская на расписание Люблино
					 * так как Братиславская в статусе реновация
					 */
					if ($url[0] === 'schedule' && $club === 'bratislavskaya') {
						Clubs::setCurrentClub('lyublino');
						$club = Clubs::getCurClub();
					}

					header('Location: https://' . $_SERVER['HTTP_HOST'] . '/' . $url[0] . '/' . $club . '/');
					exit();
				}

				if ($page = $arPageList[$url[0]]->unsel) {
					return ['page' => $page, 'params' => ['page' => $url[0]]];
				}
				break;
			//Страница с выбором клуба
			case 2:
				if (
					($page = $arPageList[$url[0]]->sel)
					&& Clubs::setCurrentClub($url[1])
					&& self::existCheck($url[0], $url[1], $arPageList)
				) {
					/**
					 * Редирект для страницы расписания для клуба Братиславская на расписание Люблино
					 * так как Братиславская в статусе реновация
					 */
					if ($url[0] === 'schedule' && $url[1] === 'bratislavskaya') {
						Clubs::setCurrentClub('lyublino');
						$club = Clubs::getCurClub();
						header('Location: https://' . $_SERVER['HTTP_HOST'] . '/' . $url[0] . '/' . $club . '/');
						exit();
					}

					//Меняется клуб, а хедер уже в буфере, меню и другую верстку нужно обновить с данными от нового клуба
					$APPLICATION->RestartBuffer();
					$APPLICATION->IncludeComponent(
						'terfit:front',
						'header',
						[]
					);
					return ['page' => $page,'params' => ['page' => $url[0],'club' => $url[1]]];
				}
				break;
			//Случай детальной страницы тренеров и услуг
			case 3:
				if (($page = self::DIFFERENT_PAGES[$url[0]. '/' .$url[1]]['template'])) {
					//Проверка на существование символьного кода тренеров или услуг
					if (self::ELEM_CLASS[$url[0]]::getList([
						'select' => ['ID'],
						'filter' => ['CODE' => $url[2], 'ACTIVE' => 'Y'],
						'cache'  => [
							'ttl'         => Cache::DAY,
						],
					])->fetch()
					) {
						return ['page' => $page, 'params' => ['page' => $url[0],'code' => $url[2]]];
					}
				}
				break;
		}
		Tools::process404('404 not found', true, true, true, false);
		return [];
	}

	/**
	 * Проверка на существование страницы
	 * @param string $page
	 * @param string $club
	 * @param array<string, PageListElement> $arPageList
	 * @return bool
	 */
	private static function existCheck(string $page, string $club, array $arPageList): bool
	{
		if ($method = $arPageList[$page]->getExistCheck()) {
			return $method($club);
		}

		return true;
	}

	/**
	 * Отсюда стартует работа компонента, больше сказать и нечего как бы
	 *
	 * @throws JsonException
	 */
	public function executeComponent()
	{
		/**
		 * @var array<string, PageListElement> $arPageList
		 */
		$arPageList = static::getPagesList();
		$result = self::getComponentByUrl($arPageList);
		$this->arParams = array_merge($this->arParams, $result['params']);
		$templateName = $result['page'];
		$page = $result['params']['page'];

		$iblockDependence = $arPageList[$page]->iblockDependence;

		$this->setTemplateName($templateName);

		if(!isset($this->arParams["CACHE_TIME"])){
			$this->arParams["CACHE_TIME"] = Cache::DAY;
		}

		$this->arParams["CACHE_TYPE"] = $arPageList[$page]->cached ? 'A' : 'N';


		if($this->arParams["CACHE_TYPE"] !== 'N') {
			$currentClubCode = Clubs::getCurClub();
			$cacheId = $page . $currentClubCode . $templateName;
			$cachePath = "/" . SITE_ID . $this->GetRelativePath();
			if ($this->startResultCache($this->arParams["CACHE_TIME"], $cacheId, $cachePath)) {
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache($cachePath);
				foreach ($iblockDependence as $iblockId) {
					$CACHE_MANAGER->RegisterTag("iblock_id_{$iblockId}");
				}
				$this->IncludeComponentTemplate();
				$CACHE_MANAGER->EndTagCache();
				$this->endResultCache();
			}
		}else{
			$this->IncludeComponentTemplate();
		}
	}
}
