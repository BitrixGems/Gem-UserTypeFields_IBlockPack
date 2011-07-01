<?
/**
 *
 * Пользовательское свойство главного модуля "Связь с элементом в виде списка"
 *
 * @author: Sergey Leshchenko [mailto:prevedgreat@gmail.com]
 * Date: 2011-02-15 (15 Feb 2011)
 * Version: 0.0.3
 *
 */

// Регистрируем обработчик события главного модуля OnUserTypeBuildList
// Событие создается при построении списка типов пользовательских свойств
AddEventHandler('main', 'OnUserTypeBuildList', array('CUserTypeIBlockElementList', 'GetUserTypeDescription'), 5000);
class CUserTypeIBlockElementList {
	// ---------------------------------------------------------------------
	// Общие параметры методов класса:
	// @param array $arUserField - метаданные (настройки) свойства
	// @param array $arHtmlControl - массив управления из формы (значения свойств, имена полей веб-форм и т.п.)
	// ---------------------------------------------------------------------

	// Функция регистрируется в качестве обработчика события OnUserTypeBuildList
	function GetUserTypeDescription() {
		return array(
			// уникальный идентификатор
			'USER_TYPE_ID' => 'iblock_element_list',
			// имя класса, методы которого формируют поведение типа
			'CLASS_NAME' => 'CUserTypeIBlockElementList',
			// название для показа в списке типов пользовательских свойств
			'DESCRIPTION' => 'Связь с элементами инфоблока в виде списка',
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
	// она же вызывается (в цикле) и при выводе формы редактирования множественного свойства
	// @return string - HTML для вывода
	function GetEditFormHTML($arUserField, $arHtmlControl) {
		$iIBlockId = intval($arUserField['SETTINGS']['IBLOCK_ID']);
		$sReturn = '';
		$sReturn .= '<div>'.CUserTypeIBlockElementList::_getItemFieldHTML($arHtmlControl['VALUE'], $iIBlockId, $arHtmlControl['NAME']).'</div>';
		return $sReturn;
	}

	// Функция вызывается при выводе фильтра на странице списка
	// @return string - HTML для вывода
	function GetFilterHTML($arUserField, $arHtmlControl) {
		//$sVal = intval($arHtmlControl['VALUE']);
		//$sVal = $sVal > 0 ? $sVal : '';
		//return '<input type="text" name="'.$arHtmlControl['NAME'].'" size="20" value="'.$sVal.'" />';
		return CUserTypeIBlockElementList::GetEditFormHTML($arUserField, $arHtmlControl);
	}

	// Функция вызывается при выводе значения свойства в списке элементов
	// @return string - HTML для вывода
	function GetAdminListViewHTML($arUserField, $arHtmlControl) {
		$iElementId = intval($arHtmlControl['VALUE']);
		if($iElementId > 0) {
			$arElements = CUserTypeIBlockElementList::_getElements($arUserField['SETTINGS']['IBLOCK_ID']);
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
		if(!empty($arHtmlControl['VALUE']) && is_array($arHtmlControl['VALUE'])) {
			$arElements = CUserTypeIBlockElementList::_getElements($arUserField['SETTINGS']['IBLOCK_ID']);
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
	// она же вызывается (в цикле) и для множественного свойства
	// @return string - HTML для вывода
	function GetAdminListEditHTML($arUserField, $arHtmlControl) {
		return CUserTypeIBlockElementList::GetEditFormHTML($arUserField, $arHtmlControl);
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
	// @param int $iIBlockId - ID информационного блока для поиска элементов
	// @param string $sFieldName - имя для поля веб-формы
	// @return string - HTML для вывода
	// @private
	function _getItemFieldHTML($iValue, $iIBlockId, $sFieldName) {
		$sReturn = '';
		// получим массив всех элементов инфоблока
		$arElements = CUserTypeIBlockElementList::_getElements($iIBlockId);
		$sReturn = '<select size="1" name="'.$sFieldName.'">
		<option value=""> </option>';
		foreach($arElements as $arItem) {
			$sReturn .= '<option value="'.$arItem['ID'].'"';
			if($iValue == $arItem['ID']) {
				$sReturn .= ' selected="selected"';
			}
			$sReturn .= '>'.$arItem['NAME'].'</option>';
		}
		$sReturn .= '</select>';
		return $sReturn;
	}

	// Функция генерации массива элементов тнфоблока
	// @param int $iIBlockId - ID информационного блока для поиска элементов
	// @param bool $bResetCache - перезаписать "виртуальный кэш" для инфоблока
	// @return array - массив элементов инфоблока с ключами = идентификаторам элементов инфоблока
	// @private
	function _getElements($iIBlockId = false, $bResetCache = false) {
		static $arVirtualCache = array();
		$arReturn = array();
		$iIBlockId = intval($iIBlockId);
		if(!isset($arVirtualCache[$iIBlockId]) || $bResetCache) {

			// добавлено 2010-12-08 (YYYY-MM-DD)
			if(!CModule::IncludeModule('iblock')) {
				return $arReturn;
			}

			if($iIBlockId > 0) {
				$arFilter = array(
					'IBLOCK_ID' => $iIBlockId
				);
				$arSelect = array(
					'ID',
					'NAME',
					'IBLOCK_ID',
					'IBLOCK_TYPE_ID'
				);
				$rsItems = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
				while($arItem = $rsItems->GetNext(false, false)) {
					// добавлено 2011-02-15 для GetList
					$arItem['VALUE'] = $arItem['NAME'];
					$arReturn[$arItem['ID']] = $arItem;
				}
			}
			$arVirtualCache[$iIBlockId] = $arReturn;
		} else {
			$arReturn = $arVirtualCache[$iIBlockId];
		}
		return $arReturn;
	}

	// добавлено 2011-02-15
	function GetList($arUserField) {
		$dbReturn = new CDBResult;
		$arElements = self::_getElements($arUserField['SETTINGS']['IBLOCK_ID']);
		$dbReturn->InitFromArray($arElements);
		return $dbReturn;
	}
}
