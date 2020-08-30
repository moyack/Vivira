/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortalCBTNav.js
 * Javascript functions for CBT Navigator
 *
 * @version 1.41
 */

// Toggle for on board
function NavCatToggle(cbtID, boardID, mode)
{
	if(!mode)
		var cstat = document.getElementById("pmxcbt"+ cbtID +".brd."+ boardID).style.display;
	else
		var cstat = (mode == "1" ? "none" : "");

	if(cstat == "none")
		$(document.getElementById("pmxcbt"+ cbtID +".brd."+ boardID)).slideDown('fast', function(){document.getElementById("pmxcbt"+ cbtID +".img."+ boardID).src = pmxCBTimages[(cstat == "none" ? 0 : 1)];});
	else
		$(document.getElementById("pmxcbt"+ cbtID +".brd."+ boardID)).slideUp('fast', function(){document.getElementById("pmxcbt"+ cbtID +".img."+ boardID).src = pmxCBTimages[(cstat == "none" ? 0 : 1)];});

	if(!mode)
	{
		var cook = "0.";
		for(var i = 0; i < (pmxCBTallBoards[cbtID]).length; i++)
		{
			if(pmxCBTallBoards[cbtID][i] == boardID)
				cook += (cstat == "none" ? pmxCBTallBoards[cbtID][i] +"." : ".");
			else
				cook += (document.getElementById("pmxcbt"+ cbtID +".brd."+ pmxCBTallBoards[cbtID][i]).style.display == "none" ? "." : pmxCBTallBoards[cbtID][i] +".");
		}
		pmxCookie("set", "cbtstat"+ cbtID, cook);
	}
}

// Toggle for all boards
function NavCatToggleALL(cbtID, mode)
{
	var cook = "0.";
	for(var i = 0; i < pmxCBTallBoards[cbtID].length; i++)
	{
		NavCatToggle(cbtID, pmxCBTallBoards[cbtID][i], mode);
		cook = cook + (mode == "0" ? "." : pmxCBTallBoards[cbtID][i] +".");
	}
	pmxCookie("set", "cbtstat"+ cbtID, cook);
}
/* EOF */