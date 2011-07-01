<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if(!empty($arResult['VALUE'])) {
	?><div class="uf_iblock_element"><?
		foreach($arResult['VALUE'] as $sValue) {
			?><span class="value-item"><?
				echo $sValue;
			?></span><?
		}
	?></div><?
}
