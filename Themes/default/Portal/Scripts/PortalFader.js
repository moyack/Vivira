/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortalFader.js
 * Javascript functions for Opac Fader
 *
 * @version 1.41
 */

var FaderElm;
var FaderOpts; 
function NewFaderHeight()
{
	PmxOpacFader.prototype.FaderSetHeight(FaderElm);
}

function PmxOpacFader(aOptions)
{
	fSetFavicon();
	var elm = document.getElementById(aOptions.fadeContId);
	FaderOpts = aOptions;
	FaderElm = elm;
	this.opt = aOptions;
	elm.style.height = this.FaderSetHeight(elm) +'px';
	this.FadeChangeOpac('0.0');
	elm.innerHTML = this.opt.fadeData[this.opt.fadeCsr];
	fSetFavicon();
	this.FadeChangeOpac('1.0');
	this.FadeUp(true);
}

PmxOpacFader.prototype.FaderSetHeight = function(elm)
{
	FaderHeight = 0;
	elm.style.height = null;
	current = elm.innerHTML;
	for(var i = 0; i < FaderOpts.fadeData.length; i++)
	{
		elm.innerHTML = FaderOpts.fadeData[i];
		if(elm.offsetHeight > FaderHeight)
			FaderHeight = elm.offsetHeight;
	}
	elm.style.height = FaderHeight +'px';
	elm.innerHTML = current;
	FaderOpts.fadeIniHeight = FaderHeight;
}

PmxOpacFader.prototype.FadeUp = function(start)
{
	if(start == null)
		this.FadeChangeData();

	$('#' + this.opt.fadeName).animate({opacity:'1.0'}, this.opt.fadeUptime[this.opt.fadeCsr]);
	setTimeout(this.opt.fadeName + '.FadeDown()', this.opt.fadeUptime[this.opt.fadeCsr] + this.opt.fadeHoldtime[this.opt.fadeCsr]);
}

PmxOpacFader.prototype.FadeDown = function()
{
	$('#' + this.opt.fadeName).animate({opacity:'0.0'}, this.opt.fadeDowntime[this.opt.fadeCsr]);
	setTimeout(this.opt.fadeName + '.FadeUp()', this.opt.fadeDowntime[this.opt.fadeCsr]);
}

PmxOpacFader.prototype.FadeChangeOpac = function(iOpac)
{
	$('#' + this.opt.fadeName).css('opacity', iOpac);
}

PmxOpacFader.prototype.FadeChangeData = function()
{
	this.opt.fadeCsr++;
	if(this.opt.fadeCsr >= this.opt.fadeData.length)
		this.opt.fadeCsr = 0;
	var elm = document.getElementById(this.opt.fadeContId);
	elm.innerHTML = this.opt.fadeData[this.opt.fadeCsr];
	fSetFavicon();
	pmxCookie('set', this.opt.fadeContId, this.opt.fadeCsr);
}
/* EOF */