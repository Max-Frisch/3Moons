{block name="title" prepend}{$LNG.lm_messages}{/block}
{block name="content"}

<div class="content_page">
	<div class="title">
		{$LNG.mg_overview}<span id="loading" style="display:none;"> ({$LNG.loading})</span>
	</div>

	<div class="message-grid">
		{foreach $CategoryList as $CategoryID => $CategoryRow}
		<a href="#" class="message-card" style="border-left-color:{$CategoryRow.color};" onclick="Message.getMessages({$CategoryID});return false;">
			<span class="message-card-title" style="color:{$CategoryRow.color};">{$LNG.mg_type.{$CategoryID}}</span>
			<span class="message-card-count"><span id="unread_{$CategoryID}">{$CategoryRow.unread}</span>/<span id="total_{$CategoryID}">{$CategoryRow.total}</span></span>
		</a>
		{/foreach}
	</div>

	<div class="title" style="margin: 0 -5px 0 -5px;">
		{$LNG.mg_game_operators}
	</div>

	<table style="width:100%;table-layout:fixed;">
		{foreach $OperatorList as $OperatorName => $OperatorEmail}
		<tr>
			<td>{$OperatorName}<a href="mailto:{$OperatorEmail}" title="{$LNG.mg_write_mail_to_ops} {{$OperatorName}}"> <i class="far fa-envelope" title="Message privé" style="font-size: 10px;"></i></a></td>
		</tr>
		{/foreach}
	</table>
</div>

{/block}
{block name="script" append}
{if !empty($category)}
<script>$(function() {
	Message.getMessages({$category}, {$side});
})</script>
{/if}
{/block}