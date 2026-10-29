{block name="title" prepend}{$LNG.lm_premium}{/block}
{block name="content"}

<div class="content_page">
	<div class="title">
		{$LNG.lm_premium} &nbsp; <span class="prem_atm">{$LNG.pr_premium_antimatter}: <b>{$antimatter}</b></span>
	</div>

	{if $premiumNotice}
	<div class="prem_notice{if $premiumNotice.type == 'error'} error{/if}">
		{if $premiumNotice.type == 'ok'}
			{sprintf($LNG.pr_premium_bought, $premiumNotice.days, $premiumNotice.cost|number)}
		{elseif $premiumNotice.reason == 'antimatter'}
			{$LNG.pr_premium_err_antimatter}
		{elseif $premiumNotice.reason == 'days'}
			{$LNG.pr_premium_err_days}
		{else}
			{$LNG.pr_premium_err_item}
		{/if}
	</div>
	{/if}

	<p class="prem_note">{$LNG.pr_premium_desc}</p>

	<div class="prem_grid">
		{foreach $premiumItems as $item}
		<div class="prem_item{if $item.active} active{/if}">
			<div class="prem_head">
				<span class="prem_name">{$item.name}</span>
				{if $item.active}
				<span class="prem_badge">{$LNG.pr_premium_active}: {$item.activeValue|number:0}{if $item.unit == '%'}%{elseif $item.unit == 'x'}x{/if}
					&nbsp; {$item.remaining}</span>
				{/if}
			</div>
			<div class="prem_desc">{$item.description}</div>
			<form action="game.php?page=premium" method="post" class="prem_form">
				<input type="hidden" name="action" value="buy">
				<input type="hidden" name="item" value="{$item.id}">
				<input type="hidden" name="kind" value="{$item.kind}">
				<span class="prem_amount">
					{if $item.kind == 'ext'}
						<b>{$item.fixed}{if $item.unit != '+'}{$item.unit}{/if}</b>
					{else}
						{if $item.unit == 'x'}x{elseif $item.unit == '%'}+{/if}
						<input type="number" name="count" value="{$item.count}" min="{$item.min}" max="{$item.max}" onchange="updatePremium('{$item.id}')" onkeyup="updatePremium('{$item.id}')">
						{if $item.unit == '%'}%{/if}
					{/if}
				</span>
				<span class="prem_days">
					{$LNG.pr_premium_days}:
					<input type="number" name="days" value="{$item.days}" min="1" max="365" onchange="updatePremium('{$item.id}')" onkeyup="updatePremium('{$item.id}')">
				</span>
				<span class="prem_cost">{$LNG.pr_premium_cost}: <b id="cost_{$item.id}">{$item.price|number}</b></span>
				<input type="submit" value="{if $item.kind == 'ext'}{$LNG.pr_premium_extend}{else}{$LNG.pr_premium_buy}{/if}">
			</form>
		</div>
		{/foreach}
	</div>
</div>

<script>
window.premiumData = {$premiumJson};
window.premiumAtm = {$antimatterRaw};
</script>
{literal}
<script>
function premiumCost(name, count, days)
{
	var d = window.premiumData[name];
	if (!d) { return 0; }
	if (d.kind === 'ext') { count = d.fixed; }
	var perDay = d.cost * Math.pow(d.factor, Math.floor(count / d.step) - d.range) * count;
	var discount = 1 - Math.min(0.5, days * 0.5 / 100);
	return Math.round(days * perDay * discount);
}
function updatePremium(name)
{
	var form = document.getElementsByName('item');
	var itemForm = null;
	for (var i = 0; i < form.length; i++) {
		if (form[i].value === name) { itemForm = form[i].form; break; }
	}
	if (!itemForm) { return; }
	var count = itemForm.querySelector('[name=count]');
	var days = itemForm.querySelector('[name=days]');
	var c = count ? Number(count.value) : window.premiumData[name].fixed;
	var d = days ? Math.max(1, Number(days.value)) : 1;
	var cost = premiumCost(name, c, d);
	var target = document.getElementById('cost_' + name);
	if (target) {
		target.textContent = cost.toLocaleString();
		target.style.color = cost > window.premiumAtm ? '#e06666' : '#8fd18f';
	}
}
</script>
{/literal}

<style>
.prem_atm { float: right; font-weight: normal; }
.prem_notice { padding: 8px; margin: 8px 0; border-left: 3px solid #8fd18f; background: rgba(143,209,143,0.12); }
.prem_notice.error { border-left-color: #e06666; background: rgba(224,102,102,0.12); }
.prem_note { color: #999; }
.prem_grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 10px; }
.prem_item { padding: 10px; border: 1px solid rgba(255,255,255,0.12); border-radius: 6px; }
.prem_item.active { border-color: rgba(143,209,143,0.55); }
.prem_head { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 4px; }
.prem_name { font-weight: bold; }
.prem_badge { color: #8fd18f; font-size: 12px; }
.prem_desc { color: #999; font-size: 12px; min-height: 30px; }
.prem_form { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 6px; }
.prem_form input[type=number] { width: 62px; }
.prem_cost b { color: #8fd18f; }
</style>

{/block}
