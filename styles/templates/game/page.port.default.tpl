{block name="title" prepend}{$LNG.lm_blackmarket}{/block}
{block name="content"}
<div class="content_page">
	<div class="title">{$LNG.lm_blackmarket}</div>
	<table style="width:100%;">
		<tbody>
			{foreach $tiles as $tile}
			<tr>
				<td style="width:280px;"><a href="game.php?{$tile[0]}">{$LNG[$tile[1]]}</a></td>
				<td>{$LNG[$tile[2]]}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/block}
