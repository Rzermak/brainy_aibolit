{if !$report.summary}
    Сканирование сайта не проводилось
{else}
    Последнее сканирование: {$report.summary.report_time|date_format:"%Y-%m-%d %H:%M:%S"}<br>
    Сканируемая директория: {$report.summary.scan_path}<br>
    Проверено файлов: {$report.summary.total_files}<br>
    Затрачено времени: {$report.summary.scan_time}<br>
    Версия антивирусных баз: {$report.summary.ai_version}<br>
    <hr>
    <table class="table report_list">
        <tr>
            <th>Файл/Фрагмент кода</th>
            <th>Статус</th>
            <th>Тип</th>
            <th>Уязвимость</th>
            <th>Размер</th>
            <th>Дата</th>
            <th></th>
        </tr>
        {foreach from=$report key=virus_type item=report_type}
            {if $virus_type eq 'summary'}
                {continue}
            {/if}
            {foreach from=$report_type item=item}
                <tr class="{if $item.manual_editing == '1' or $item.manual_deleting == '1'}manual{/if}">
                    <td class="file_data">
                        <div class="aibolit_report_file{if $virus_type eq 'vulners'} __warning{/if}">{$item.fn}</div>
                        <div class="aibolit_report_code">{$item.sig|escape:'htmlall'}</div>
                    </td>
                    <td class="status_manual">
                        {if $item.manual_deleting == '1'}
                            Удалено вручную
                        {elseif $item.manual_editing == '1'}
                            Изменено вручную
                        {else}
                            Обнаружено
                        {/if}
                    </td>
                    <td>{$virus_type}</td>
                    <td>
                        {if $item.sn}
                            {$item.sn}
                        {else}
                            -
                        {/if}
                    </td>
                    <td>
                        {if $item.sz lt 1024}
                            {$item.sz} B
                        {elseif $item.sz lt 1048576}
                            {$size = $item.sz / 1024}
                            {$size|string_format:"%.2f"} KB
                        {elseif $item.sz lt 1073741824}
                            {$size = $item.sz / 1024 / 1024}
                            {$size|string_format:"%.2f"} MB
                        {else}
                            {$size = $item.sz / 1024 / 1024 / 1024}
                            {$size|string_format:"%.2f"} GB
                        {/if}
                    </td>
                    <td>{$item.et|date_format:"%Y-%m-%d %H:%M:%S"}</td>
                    <td>
                        {if $item.manual_deleting != '1'}
                            <div class="btn btn-eye" data-aibolit-viewfile="true" data-file="{$item.fn}" data-site="{$site}">Посмотреть</div>
                            <div class="btn btn-trash" data-aibolit-removefile="true" data-file="{$item.fn}" data-site="{$site}">Удалить</div>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        {/foreach}
    </table>
{/if}