<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

// значение по умолчанию (to do)
if($arParams['arUserField']['ENTITY_VALUE_ID'] <= 0 && !empty($arParams['arUserField']['SETTINGS']['DEFAULT_VALUE'])) {
	$arResult['VALUE'] = array(
		$arParams['arUserField']['SETTINGS']['DEFAULT_VALUE']
	);
} else {
	// удалим пустое значение
	$arResult['VALUE'] = array_filter($arResult['VALUE']);
}

// выводим список выбора
$sDisabled = $arParams['arUserField']['EDIT_IN_LIST'] != 'Y' ? ' disabled="disabled"' : '';
if($arParams['arUserField']['MULTIPLE'] == 'Y') {
	$iListHeight = !empty($arParams['arUserField']['SETTINGS']['LIST_HEIGHT']) ? $arParams['arUserField']['SETTINGS']['LIST_HEIGHT'] : 5;
	echo '<select multiple="multiple" name="'.$arParams['arUserField']['FIELD_NAME'].'" size="'.$iListHeight.'"'.$sDisabled.'>';
} else {
	echo '<select name="'.$arParams['arUserField']['FIELD_NAME'].'"'.$sDisabled.'>';
}

foreach($arParams['arUserField']['USER_TYPE']['FIELDS'] as $iItemID => $sValue) {
	$sSelected = in_array($iItemID, $arResult['VALUE']) ? ' selected="selected"' : '';
	?><option value="<?=$iItemID?>"<?=$sSelected?>><?
		echo $sValue;
	?></option><?
}
echo '</select>';
