<?
/**
 *
 * Пользовательское свойство главного модуля "Связь с элементом"
 *
 * @author: Sergey Leshchenko [mailto:prevedgreat@gmail.com]
 * Date: 2010-12-00 (08 Dec 2010)
 * Version: 0.0.2
 *
 */

// Регистрируем обработчик события главного модуля OnUserTypeBuildList
// Событие создается при построении списка типов пользовательских свойств
AddEventHandler('main', 'OnUserTypeBuildList', array('CUserTypeIBlockElement', 'GetUserTypeDescription'), 5000);
class CUserTypeIBlockElement {
	// ---------------------------------------------------------------------
	// Общие параметры методов класса:
	// @param array $arUserField - метаданные (настройки) свойства
	// @param array $arHtmlControl - массив управления из формы (значения свойств, имена полей веб-форм и т.п.)
	// ---------------------------------------------------------------------

	// Функция регистрируется в качестве обработчика события OnUserTypeBuildList
	function GetUserTypeDescription() {
		return array(
			// уникальный идентификатор
			'USER_TYPE_ID' => 'iblock_element',
			// имя класса, методы которого формируют поведение типа
			'CLASS_NAME' => 'CUserTypeIBlockElement',
			// название для показа в списке типов пользовательских свойств
			'DESCRIPTION' => 'Связь с элементами инфоблока',
			// базовый тип на котором будут основаны операции фильтра
			'BASE_TYPE' => 'int',
		);
	}

	// Функция вызывается при добавлении нового свойства
	// для конструирования SQL запроса создания столбца значений свойства
	// @return string - SQL
	function GetDBColumnType($arUserField) {
		switch(strtolower($GLOBALS['DB']->type)) {
			case 'mysql':
				return 'int(18)';
			break;
			case 'oracle':
				return 'number(18)';
			break;
			case 'mssql':
				return "int";
			break;
		}
	}

	// Функция вызывается перед сохранением метаданных (настроек) свойства в БД
	// @return array - массив уникальных метаданных для свойства, будет сериализован и сохранен в БД
	function PrepareSettings($arUserField) {
		// инфоблок, с элементами которого будет выполняться связь
		$iIBlockId = intval($arUserField['SETTINGS']['IBLOCK_ID']);
		return array(
			'IBLOCK_ID' => $iIBlockId > 0 ? $iIBlockId : 0
		);
	}

	// Функция вызывается при выводе формы метаданных (настроек) свойства
	// @param bool $bVarsFromForm - флаг отправки формы
	// @return string - HTML для вывода
	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm) {
		$result = '';

		// добавлено 2010-12-08 (YYYY-MM-DD)
		if(!CModule::IncludeModule('iblock')) {
			return $result;
		}

		// текущие значения настроек 
		if($bVarsFromForm) {
			$value = $GLOBALS[$arHtmlControl['NAME']]['IBLOCK_ID'];
		} elseif(is_array($arUserField)) {
			$value = $arUserField['SETTINGS']['IBLOCK_ID'];
		} else {
			$value = '';
		}
		// выведем выпадающий список выбора связываемого инфоблока
		$result .= '
		<tr style="vertical-align: top;">
			<td>Информационный блок по умолчанию:</td>
			<td>
				'.GetIBlockDropDownList($value, $arHtmlControl['NAME'].'[IBLOCK_TYPE_ID]', $arHtmlControl['NAME'].'[IBLOCK_ID]').'
			</td>
		</tr>
		';
		return $result;
	}

	// Функция валидатор значений свойства
	// вызвается в $GLOBALS['USER_FIELD_MANAGER']->CheckField() при добавлении/изменении
	// @param array $value значение для проверки на валидность
	// @return array массив массивов ("id","text") ошибок
	function CheckFields($arUserField, $value) {
		$aMsg = array();
		return $aMsg;
	}

	// Функция вызывается при выводе формы редактирования значения свойства
	// @return string - HTML для вывода
	function GetEditFormHTML($arUserField, $arHtmlControl) {
		$iIBlockId = intval($arUserField['SETTINGS']['IBLOCK_ID']);
		$sReturn = '';
		// получим элементы инфоблока по значению свойства, передаваемым в $arHtmlControl['VALUE']
		$arElements = CUserTypeIBlockElement::_getElements($arHtmlControl['VALUE']);
		// html поля веб-формы для текущего значения
		$sReturn .= '<div>'.CUserTypeIBlockElement::_getItemFieldHTML($arHtmlControl['VALUE'], $iIBlockId, $arElements, $arHtmlControl['NAME']).'</div>';
		return $sReturn;
	}

	// Функция вызывается при выводе формы редактирования значения множественного свойства
	// @return string - HTML для вывода
	function GetEditFormHTMLMulty($arUserField, $arHtmlControl) {
		$iIBlockId = intval($arUserField['SETTINGS']['IBLOCK_ID']);
		// получим элементы инфоблока по значениям свойства, передаваемым в массиве значений $arHtmlControl['VALUE']
		$arElements = CUserTypeIBlockElement::_getElements($arHtmlControl['VALUE']);

		$sReturn = '<table cellspacing="0" id="tb'.md5($arHtmlControl['NAME']).'">';
		// html поля веб-формы для каждого значения свойства
		foreach($arHtmlControl['VALUE'] as $iKey => $iValue) {
			$sReturn .= '<tr><td><div>'.CUserTypeIBlockElement::_getItemFieldHTML($iValue, $iIBlockId, $arElements, $arHtmlControl['NAME']).'</div></td></tr>';
		}
		// html поля веб-формы для добавления нового значения свойства
		$sReturn .= '<tr><td><div>'.CUserTypeIBlockElement::_getItemFieldHTML(0, $iIBlockId, array(), $arHtmlControl['NAME']).'</div></td></tr>';
		// html полей для кнопки Добавить... (режим множественного выбора элементов)
		$sReturn .= '<tr><td><div>'.CUserTypeIBlockElement::_getItemFieldHTML(0, $iIBlockId, array(), $arHtmlControl['NAME'], 'y').'</div></td></tr>';
		$sReturn .= '</table>';
		return $sReturn;
	}

	// Функция вызывается при выводе фильтра на странице списка
	// @return string - HTML для вывода
	function GetFilterHTML($arUserField, $arHtmlControl) {
		//$sVal = intval($arHtmlControl['VALUE']);
		//$sVal = $sVal > 0 ? $sVal : '';
		//return '<input type="text" name="'.$arHtmlControl['NAME'].'" size="20" value="'.$sVal.'" />';
		return CUserTypeIBlockElement::GetEditFormHTML($arUserField, $arHtmlControl);
	}

	// Функция вызывается при выводе значения свойства в списке элементов
	// @return string - HTML для вывода
	function GetAdminListViewHTML($arUserField, $arHtmlControl) {
		$iElementId = intval($arHtmlControl['VALUE']);
		if($iElementId > 0) {
			$arElements = CUserTypeIBlockElement::_getElements($iElementId);
			// выводим в формате: [ID элемента] имя элемента (если найдено)
			return '['.$iElementId.'] '.(isset($arElements[$iElementId]) ? $arElements[$iElementId]['NAME'] : '');
		} else {
			return '&nbsp;';
		}
	}

	// Функция вызывается при выводе значения множественного свойства в списке элементов
	// @return string - HTML для вывода
	function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl) {
		$sReturn = '';
		static $bWasJs = false;
		// костыль для добавления js-функции, отвечающей за добавление новых полей значения множественного свойства
		// т.к. в режиме массового редактирования значений свойства из-за ajax изменяется область видимости функций js
		// добавляется один раз на странице
		if(!$bWasJs) {
			$bWasJs = true;
			// здесь пляски с бубном из-за гениальности передачи данных 
			// из попап-окна /bitrix/admin/iblock_element_search.php на вызывающую страницу
			ob_start();
			?><script type="text/javascript">
				var oIBListUF = {
					oCounter: {},
					addNewRowIBListUF: function(mFieldCounterName, sTableId, sFieldName, sOpenWindowUrl, sSpanId) {
						var oTbl = document.getElementById(sTableId);
						var oRow = oTbl.insertRow(oTbl.rows.length-1);
						var oCell = oRow.insertCell(-1);
						if(!this.oCounter.mFieldCounterName) {
							this.oCounter.mFieldCounterName = 0;
						}
						var sK = 'n'+this.oCounter.mFieldCounterName;
						this.oCounter.mFieldCounterName = parseInt(this.oCounter.mFieldCounterName) + 1;
						sOpenWindowUrl += '&k='+sK;
						sSpanId += '_'+sK;
						oCell.innerHTML = '<input type="text" id="'+sFieldName+'['+sK+']" name="'+sFieldName+'['+sK+']" value="" size="5" />';
						oCell.innerHTML += '<input type="button" value="..." onclick="jsUtils.OpenWindow(\''+sOpenWindowUrl+'\', 600, 500);" />';
						oCell.innerHTML += '&nbsp;<span id="'+sSpanId+'"></span>';
				}
				};
			</script><?
			$sReturn .= ob_get_clean();
		}
		
		if(!empty($arHtmlControl['VALUE']) && is_array($arHtmlControl['VALUE'])) {
			$arElements = CUserTypeIBlockElement::_getElements($arHtmlControl['VALUE']);
			$arPrint = array();
			// выводим в формате: [ID элемента] имя элемента (если найдено) с разделителем " / " для каждого значения
			foreach($arHtmlControl['VALUE'] as $iElementId) {
				$arPrint[] = '['.$iElementId.'] '.(isset($arElements[$iElementId]) ? $arElements[$iElementId]['NAME'] : '');
			}
			$sReturn .= implode(' / ', $arPrint);
		} else {
			$sReturn .=  '&nbsp;';
		}
		return $sReturn;
	}

	// Функция вызывается при выводе значения свойства в списке элементов в режиме редактирования
	// @return string - HTML для вывода
	function GetAdminListEditHTML($arUserField, $arHtmlControl) {
		return CUserTypeIBlockElement::GetEditFormHTML($arUserField, $arHtmlControl);
	}

	// Функция вызывается при выводе множественного значения свойства в списке элементов в режиме редактирования
	// @return string - HTML для вывода
	function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl) {
		$iIBlockId = intval($arUserField['SETTINGS']['IBLOCK_ID']);
		$arElements = CUserTypeIBlockElement::_getElements($arHtmlControl['VALUE']);

		// поля редактирования значений свойства 
		$sTableId = 'tb'.md5($arHtmlControl['NAME']);
		$sReturn = '<table cellspacing="0" id="'.$sTableId.'">';
		foreach($arHtmlControl['VALUE'] as $iKey => $iValue) {
			$sReturn .= '<tr><td><div>'.CUserTypeIBlockElement::_getItemFieldHTML($iValue, $iIBlockId, $arElements, $arHtmlControl['NAME']).'</div></td></tr>';
		}
		// поле добавления нового значения свойства 
		$sReturn .= '<tr><td><div>'.CUserTypeIBlockElement::_getItemFieldHTML(0, $iIBlockId, array(), $arHtmlControl['NAME']).'</div></td></tr>';

		// кнопка Добавить... (вызывает js-функцию, которая была добавлена методом GetAdminListViewHTMLMulty)
		$sFieldName_ = str_replace('[]', '', $arHtmlControl['NAME']);
		$mFieldCounterName = md5($sFieldName_);
		$sOpenWindowUrl = '/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$iIBlockId.'&amp;n='.$sFieldName_.'&amp;m=n';
		$sSpanId = 'sp_'.$mFieldCounterName;
		$sReturn .= '<tr><td><div><input type="button" onclick="oIBListUF.addNewRowIBListUF(\''.$mFieldCounterName.'\', \''.$sTableId.'\', \''.$sFieldName_.'\', \''.$sOpenWindowUrl.'\', \''.$sSpanId.'\')" value="Добавить" /></div></td></tr>';
		$sReturn .= '</table>';
		return $sReturn;
	}

	// Функция должна вернуть представление значения поля для поиска
	// @return string - посковое содержимое
	function OnSearchIndex($arUserField) {
		if(is_array($arUserField['VALUE'])) {
			return implode("\r\n", $arUserField['VALUE']);
		} else {
			return $arUserField['VALUE'];
		}
	}

	// Функция вызывается перед сохранением значений в БД
	// @param mixed $value - значение свойства
	// @return string - значение для вставки в БД
	function OnBeforeSave($arUserField, $value) {
		if(intval($value) > 0) {
			return intval($value);
		}
	}

	// Функция генерации html для поля редактирования свойства
	// @param int $iValue - значение свойства
	// @param int $iIBlockId - ID информационного блока для поиска элементов по умолчанию
	// @param array $arElements - массив элементов инфоблока с ключами = идентификаторам элементов инфоблока
	// @param string $sFieldName - имя для поля веб-формы
	// @param string $sMulty - n|y - поэлементная (n) или множественная вставка значений 
	// @return string - HTML для вывода
	// @private
	function _getItemFieldHTML($iValue, $iIBlockId, $arElements, $sFieldName, $sMulty = 'n') {
		$sReturn = '';
		$iValue = intval($iValue);
		$sKey = randstring(3);
		$sName = 'UF_IBELEMENT_'.randstring(3);
		$sRandId = $sName.'_'.$sKey;
		$sElementName = '';
		if(!empty($arElements[$iValue])) {
			$sElementName = '<a href="'.BX_PERSONAL_ROOT.'/admin/iblock_element_edit.php?ID='.$arElements[$iValue]['ID'].'&type='.$arElements[$iValue]['IBLOCK_TYPE_ID'].'&lang='.LANG.'&IBLOCK_ID='.$arElements[$iValue]['IBLOCK_ID'].'&find_section_section=-1">'.$arElements[$iValue]['NAME'].'</a>';
		}
		$md5Name = md5($sName);
		$sValue = $iValue > 0 ? $iValue : '';
		$sButtonValue = $sMulty == 'y' ? 'Добавить ...' : '...';
		$sReturn .= '<input type="text" name="'.$sFieldName.'" id="'.$sName.'" value="'.$sValue.'" size="5" />';
		$sReturn .= '<input type="button" value="'.$sButtonValue.'" onclick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$iIBlockId.'&amp;n='.$sName.'&amp;m='.$sMulty.'&amp;k='.$sKey.'\', 600, 500);" />';
		$sReturn .= '&nbsp;<span id="sp_'.$md5Name.'_'.$sKey.'" >'.$sElementName.'</span>';

		if($sMulty == 'y') {
			$sJsMV = 'MV_'.$md5Name;
			// уберем пустые скобки
			$sFieldName_ = str_replace('[]', '', $sFieldName);
			$sJsFuncName = 'InS'.$md5Name;
			ob_start();
			?><script type="text/javascript">
				var <?=$sJsMV?> = 0;
				var <?=$sJsFuncName?> = function(sId, sName) {
					var oTbl = document.getElementById('tb<?=md5($sFieldName)?>');
					var oRow = oTbl.insertRow(oTbl.rows.length-1);
					var oCell = oRow.insertCell(-1);
					var sK = 'n'+<?=$sJsMV?>;
					oCell.innerHTML = '<input type="text" id="<?=$sFieldName_?>['+sK+']" name="<?=$sFieldName_?>['+sK+']" value="'+sId+'" size="5" />';
					oCell.innerHTML += '<input type="button" value="..." onclick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=<?=LANG?>&amp;IBLOCK_ID=<?=$iIBlockId?>&amp;n=<?=$sFieldName_?>&amp;k='+sK+'\', 600, 500);" />';
					oCell.innerHTML += '&nbsp;<span id="sp_<?=md5($sFieldName_)?>_'+<?=sK?>+'">'+sName+'</span>';
					<?=$sJsMV?>++;
				};
			</script><?
			$sReturn .= ob_get_clean();
		}
		return $sReturn;
	}

	// Функция генерации массива элементов по значениям свойства
	// @param mixed $mElementId - значение свойства (массив или целое число)
	// @return array - массив элементов инфоблока с ключами = идентификаторам элементов инфоблока
	// @private
	function _getElements($mElementId = array()) {
		$arReturn = array();

		if(!empty($mElementId)) {

			// добавлено 2010-12-08 (YYYY-MM-DD)
			if(!CModule::IncludeModule('iblock')) {
				return $arReturn;
			}

			$arFilter = array(
				'ID' => array()
			);
			$mElementId = is_array($mElementId) ? $mElementId : array($mElementId);
			foreach($mElementId as $iValue) {
				$iValue = intval($iValue);
				if($iValue > 0) {
					$arFilter['ID'][] = $iValue;
				}
			}
			if(!empty($arFilter['ID'])) {
				$arSelect = array(
					'ID',
					'NAME',
					'IBLOCK_ID',
					'IBLOCK_TYPE_ID'
				);
				$rsItems = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
				while($arItem = $rsItems->GetNext(false, false)) {
					$arReturn[$arItem['ID']] = $arItem;
				}
			}
		}
		return $arReturn;
	}
}
