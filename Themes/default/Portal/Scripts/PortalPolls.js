/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortalPolls.js
 * Javascript functions for Poll block
 *
 * @version 1.41
 */

function pmx_VotePoll(id, elm)
{
	// check if any option selected
	var isVoted = false;
	var elmName = "pmx_pollopt" + id + "_";
	var i = 0;
	while(document.getElementById(elmName + i))
	{
		isVoted = (document.getElementById(document.getElementById(elmName + i).htmlFor).checked == true ? true : isVoted);
		i++;
	}
	if(isVoted)
	{
		pmxWinGetTop("poll"+ id);
		document.getElementById("pmx_voteform" + id).submit();
	}
	else
		alert(pmx_poll_novote_opt);
}
function pmx_ShowPollResult(id, poll)
{
	document.getElementById("pxm_allowvotepoll" + id).style.display = "none";
	document.getElementById("pxm_allowviewpoll" + id).style.display = "";
	pmx_SavePolldata(id, poll, 1);
	return false;
}
function pmx_ShowPollVote(id, poll)
{
	document.getElementById("pxm_allowviewpoll" + id).style.display = "none";
	document.getElementById("pxm_allowvotepoll" + id).style.display = "";
	pmx_SavePolldata(id, poll, 0);
	return false;
}
function pmx_ChangePollVote(id, elm)
{
	pmxWinGetTop("poll"+ id);
	document.getElementById("pmx_voteform" + id).submit();
}
function pmx_ChangeCurrentPoll(id, elm)
{
	var idx = elm.selectedIndex;
	var pollid = elm.options[idx].value;
	pmx_SavePolldata(id, pollid, 0);
	document.getElementById("pollchanged" + id).value = pollid;
	pmxWinGetTop("poll"+ id);
	document.getElementById("pmx_votechange" + id).submit();
}
function pmx_SavePolldata(id, poll, state)
{
	pmxCookie("set", "pmx_poll" + id, poll + "," + state);
}
/* EOF */