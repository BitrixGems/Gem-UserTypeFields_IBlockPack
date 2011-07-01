var UF_AddIBlockElementField = function(sContainerID) {
	var obCont = null;
	if(obCont = document.getElementById(sContainerID)) {
		var obNodesList = obCont.getElementsByTagName('div');
		if(obNodesList && obNodesList.length > 0 && obNodesList[0]) {
			var obClone = obNodesList[0].cloneNode(true);
			if(obClone) {
				var obInputList = obClone.getElementsByTagName('input');
				for(var mIdx in obInputList) {
					obInputList[mIdx].value = '';
				}
			}
			obCont.appendChild(obClone);
		}
	}
	return false;
};
