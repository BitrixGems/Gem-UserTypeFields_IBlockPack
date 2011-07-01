<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
if(!empty($arResult['VALUE']) && is_array($arResult['VALUE'])) {
	$arUserField =& $arParams['arUserField'];
	$arResult['ITEMS'] = array();
	if(CModule::IncludeModule('iblock')) {
		$arOrder = array(
			'NAME' => 'ASC'
		);
		$arFilter = array(
			'IBLOCK_ID' => $arUserField['SETTINGS']['IBLOCK_ID'],
			'ID' => $arResult['VALUE']
		);
		$arSelect = array(
			'ID',
			'IBLOCK_ID',
			'NAME',
			'CODE'
		);
		$dbItems = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
		$arResult['VALUE'] = array();
		while($arItem = $dbItems->GetNext(false, false)) {
			$arResult['VALUE'][$arItem['ID']] = $arItem['NAME'];
			$arResult['ITEMS'][] = $arItem;
		}
	}
}
