<table class="table">
	<tr>
		<th>Сайт</th>
		<th>Путь</th>
		<th>Пользователь</th>
		<th>Статус</th>
		<th>Угроз</th>
		<th>Операция</th>
		<th></th>
	</tr>
	{foreach from=$sites item=site}
        <tr>
            <td>{$site.domain}</td>
            <td>{$site.dir}</td>
            <td>{$site.user}</td>
            <td>
                {if $site.scanner.queue eq 'complete'}
                    {if $site.scanner.virus_detected}
                        <span class="aibolit_site_virus">Заражён</span>
                    {else}
                        <span class="aibolit_site_clean">Чистый</span>
                    {/if}
                {else}
                    -
                {/if}
            </td>
            <td>
                {if $site.scanner.virus_detected and $site.scanner.queue eq 'complete'}
                    <span class="aibolit_site_virus">{$site.scanner.virus_detected}</span>
                {else}
                    <span class="aibolit_site_clean">-</span>
                {/if}
            </td>
            <td>
                {if $site.scanner.queue eq 'complete'}
                    Отчёт {$site.scanner.time|date_format:"%Y-%m-%d %H:%M:%S"}
                {elseif $site.scanner.queue eq 'scanning'}
                    <i class="fa fa-spinner fa-spin"></i>
                    Сканирование
                    {if $site.scanner.progress}
                        {$site.scanner.progress}%
                    {else}
                        0%
                    {/if}
                {elseif $site.scanner.queue eq 'stopped'}
                    <i class="fa fa-spinner fa-spin"></i>
                    Остановка
                {elseif $site.scanner.queue eq 'waiting'}
                    В очереди
                {elseif $site.scanner.queue eq '-1'}
                    <span class="aibolit_scan_error">Ошибка сканирования</span>
                {else}
                    -
                {/if}
            </td>
            <td>
                {if $site.scanner.queue eq 'scanning' or $site.scanner.queue eq 'waiting'}
                    <div class="btn btn-stop" data-command="stop" data-aibolit-scan="true" data-site="{$site.domain}">Остановить</div>
                {else}
                    <div class="btn btn-play" data-command="start" data-aibolit-scan="true" data-site="{$site.domain}">Сканировать</div>
                {/if}
                {if $site.scanner.queue eq 'complete'}
                    <div class="btn btn-share" data-aibolit-reportview="true" data-site="{$site.domain}">Отчёт</div>
                {/if}
                {if $site.scanner.queue eq 'scanning' or $site.scanner.queue eq '-1'}
                    <div class="btn btn-share" data-aibolit-logsview="true" data-site="{$site.domain}">Логи</div>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>