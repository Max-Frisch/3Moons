{block name="title" prepend}{$LNG.bm_voucher}{/block}
{block name="content"}
<div class="content_page">
	<div class="title">{$LNG.bm_voucher}</div>
	{if $done}<div class="green" style="padding:6px;">{$LNG.bm_done}</div>{/if}
	{if $error}<div class="red" style="padding:6px;">{$error}</div>{/if}
	<p style="text-align:center;">{$LNG.bm_voucher_desc}</p>
	<form action="game.php?page=reward2&amp;mode=redeem" method="post" style="text-align:center;">
		<label for="code">{$LNG.bm_voucher_code}:</label>
		<input type="text" id="code" name="code" maxlength="64" autocomplete="off" style="width:260px;">
		<input type="submit" value="{$LNG.bm_redeem}">
	</form>
</div>
{/block}
