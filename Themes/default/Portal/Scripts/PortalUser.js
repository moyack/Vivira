/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortalUser.js
 * Javascript functions for User Block
 *
 * @version 1.41
 */

// show the server time with users offset
function ulClock(id)
{
	var pmx_CTime = pmx_rtcFormat[id];
	var pmx_rtc = new Date();
	pmx_rtc.setTime(pmx_rtc.getTime() + pmx_rtcOffset);
	var pmx_rtcMt = "0" + pmx_rtc.getMonth();
	var pmx_rtcD = "0" + pmx_rtc.getDate();
	var pmx_rtcH = pmx_rtc.getHours();
	var pmx_rtcM = "0" + pmx_rtc.getMinutes();
	var pmx_rtcS = "0" + pmx_rtc.getSeconds();
	var pmx_rtcAM = "am";
	if(pmx_CTime.search(/%I/) != -1)
	{
		if(pmx_rtcH == 0)
			pmx_rtcH = pmx_rtcH + 12;
		else
		{
			if(pmx_rtcH >= 12)
			{
				pmx_rtcH = pmx_rtcH > 12 ? pmx_rtcH - 12 : pmx_rtcH;
				pmx_rtcAM = "pm";
			}
		}
	}
	pmx_rtcH = "0" + pmx_rtcH;
	var pmx_rtc_values = new Array(
		pmx_rctShortDays[pmx_rtc.getDay()],
		pmx_rctDays[pmx_rtc.getDay()],
		pmx_rtcD.toString().substr(pmx_rtcD.length - 2),
		pmx_rctShortMonths[pmx_rtc.getMonth()],
		pmx_rctMonths[pmx_rtc.getMonth()],
		pmx_rtcMt.substr(pmx_rtcMt.length - 2),
		pmx_rtc.getFullYear(),
		pmx_rtc.getFullYear().toString().substr(2, 2),
		pmx_rtcH.substr(pmx_rtcH.length - 2),
		pmx_rtcH.substr(pmx_rtcH.length - 2),
		pmx_rtcM.substr(pmx_rtcM.length - 2),
		pmx_rtcS.substr(pmx_rtcS.length - 2),
		pmx_rtcAM,
		"%",
		"",
		"",
		"",
		""
	);
	for(var i = 0; i < pmx_rtcFormatTypes.length; i++)
	{
		if(pmx_CTime.search(pmx_rtcFormatTypes[i]) != -1)
			pmx_CTime = pmx_CTime.replace(pmx_rtcFormatTypes[i], pmx_rtc_values[i]);
	}
	document.getElementById("ulClock"+ id).innerHTML = pmx_CTime;
	$(document).ready(function(){
		setTimeout("ulClock("+ id +")",1000);
	});
}
/* EOF */