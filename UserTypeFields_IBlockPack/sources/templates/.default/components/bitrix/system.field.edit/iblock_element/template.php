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

$sNodeID = 'uf_'.randstring(10);
?><div class="fields-edit-block iblock_element" id="<?=$sNodeID?>"><?
	$sDisabled = $arParams['arUserField']['EDIT_IN_LIST'] != 'Y' ? ' disabled="disabled"' : '';
	foreach($arResult['VALUE'] as $iValue) {
		?><div class="field-item"><?
			$iValue = intval($iValue);
			?><input type="text" value="<?=$iValue?>" name="<?=$arParams['arUserField']['FIELD_NAME']?>"<?=$sDisabled?> /><?
		?></div><?
	}
?></div><?

if(empty($sDisabled) && $arParams['arUserField']['MULTIPLE'] == 'Y' && $arParams['SHOW_BUTTON'] != 'N') {
	?><input type="button" value="<?=GetMessage('USER_TYPE_PROP_ADD')?>" onclick="return UF_AddIBlockElementField('<?=$sNodeID?>');" /><?
}
