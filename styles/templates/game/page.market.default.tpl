{block name="title" prepend}{$LNG.lm_market}{/block}
{block name="content"}
<style type="text/css">
.market_notice { padding: 8px; margin: 8px 0; border-left: 3px solid #8fd18f; background: rgba(143,209,143,0.12); }
.market_notice.error { border-left-color: #e06666; background: rgba(224,102,102,0.12); }
.market_grid { display: flex; gap: 14px; align-items: flex-start; }
.market_left { flex: 0 0 300px; }
.market_right { flex: 1 1 auto; min-width: 0; }
.market_panel { background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.08); padding: 8px; margin-bottom: 12px; }
.market_panel h3 { margin: 0 0 8px 0; color: #f0a020; font-size: 15px; }
.market_panel table { width: 100%; border-collapse: collapse; }
.market_panel th, .market_panel td { padding: 4px 6px; border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 12px; text-align: left; }
.market_panel th { color: #9fc5e8; font-weight: normal; }
.market_form label { display: block; margin-top: 6px; font-size: 12px; color: #b8b8b8; }
.market_form select, .market_form input { width: 100%; box-sizing: border-box; }
.market_form input[type=submit], .market_form button { margin-top: 10px; width: auto; }
.market_hint { color: #b8b8b8; font-size: 11px; margin-top: 6px; }
.market_stats { float: right; font-weight: normal; font-size: 12px; }
.market_empty { color: #b8b8b8; font-size: 12px; }
</style>

<div class="content_page" style="width: 97%;">
	<div class="title">
		{$LNG.lm_market}
		<span class="market_stats">{$LNG.ach_antimatter}: <b>{$marketAntimatter}</b></span>
	</div>

	{if $marketNotice}
	<div class="market_notice{if $marketNotice.type == 'error'} error{/if}">
		{if $marketNotice.type == 'listed'}
			{sprintf($LNG.market_notice_listed, $marketNotice.amount, $marketNotice.price)}
		{elseif $marketNotice.type == 'bought'}
			{sprintf($LNG.market_notice_bought, $marketNotice.amount, $marketNotice.name, $marketNotice.price)}
		{elseif $marketNotice.type == 'removed'}
			{sprintf($LNG.market_notice_removed, $marketNotice.amount)}
		{else}
			{if $marketNotice.reason == 'items'}{$LNG.market_err_items}
			{elseif $marketNotice.reason == 'invalid'}{$LNG.market_err_invalid}
			{elseif $marketNotice.reason == 'missing'}{$LNG.market_err_missing}
			{elseif $marketNotice.reason == 'expired'}{$LNG.market_err_expired}
			{elseif $marketNotice.reason == 'own'}{$LNG.market_err_own}
			{elseif $marketNotice.reason == 'antimatter'}{$LNG.market_err_antimatter}
			{else}{$LNG.market_err_unknown}{/if}
		{/if}
	</div>
	{/if}

	<div class="market_grid">
		<div class="market_left">
			<div class="market_panel">
				<h3>{$LNG.market_sell}</h3>
				<form action="game.php?page=market" method="post" class="market_form">
					<input type="hidden" name="mode" value="sellUpgrades">
					<label>{$LNG.market_choose}</label>
					<select name="type">
						{foreach $marketSellOptions as $option}
						<option value="{$option.code}">{$option.name} [{$option.available}]</option>
						{/foreach}
					</select>
					<label>{$LNG.market_amount}</label>
					<input type="number" name="amount" min="1" max="{$marketMaxAmount}" value="1">
					<label>{$LNG.market_rate}</label>
					<input type="number" name="rate" min="1" max="{$marketMaxRate}" value="500">
					<button type="submit" class="build_submit construct_button">{$LNG.market_sell_submit}</button>
					<div class="market_hint">{sprintf($LNG.market_lot_hours, $marketLotHours)}</div>
				</form>
			</div>
		</div>

		<div class="market_right">
			<div class="market_panel">
				<h3>{$LNG.market_current}</h3>
				{if $marketLots}
				<table>
					<tr>
						<th>{$LNG.market_id}</th>
						<th>{$LNG.market_upgrade}</th>
						<th>{$LNG.market_amount}</th>
						<th>{$LNG.market_total}</th>
						<th>{$LNG.market_buy}</th>
					</tr>
					{foreach $marketLots as $lot}
					<tr>
						<td>{$lot.id}</td>
						<td>{$lot.name}</td>
						<td>{$lot.amount}</td>
						<td>{$lot.priceText} {$LNG.ach_antimatter}</td>
						<td>
							<form action="game.php?page=market" method="post">
								<input type="hidden" name="mode" value="BuyUpgrade">
								<input type="hidden" name="id" value="{$lot.id}">
								<button type="submit" class="build_submit construct_button" style="width: auto;">{$LNG.market_buy}</button>
							</form>
						</td>
					</tr>
					{/foreach}
				</table>
				{else}
				<div class="market_empty">{$LNG.market_empty}</div>
				{/if}
			</div>

			<div class="market_panel">
				<h3>{$LNG.market_your}</h3>
				{if $marketOwnLots}
				<table>
					<tr>
						<th>{$LNG.market_id}</th>
						<th>{$LNG.market_upgrade}</th>
						<th>{$LNG.market_amount}</th>
						<th>{$LNG.market_price}</th>
						<th>{$LNG.market_remove}</th>
					</tr>
					{foreach $marketOwnLots as $lot}
					<tr>
						<td>{$lot.id}</td>
						<td>{$lot.name}</td>
						<td>{$lot.amount}</td>
						<td>{$lot.priceText} {$LNG.ach_antimatter}</td>
						<td>
							<form action="game.php?page=market" method="post">
								<input type="hidden" name="mode" value="remove">
								<input type="hidden" name="id" value="{$lot.id}">
								<button type="submit" class="build_submit construct_button" style="width: auto;">{$LNG.market_remove}</button>
							</form>
						</td>
					</tr>
					{/foreach}
				</table>
				{else}
				<div class="market_empty">{$LNG.market_no_own}</div>
				{/if}
			</div>
		</div>
	</div>
</div>
{/block}
