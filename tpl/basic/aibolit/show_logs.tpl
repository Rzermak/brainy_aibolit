{if $module_log}
<pre class="aibolit-logs" data-aibolit-logs="true">{$module_log}</pre>
{/if}
{if $scan_log}
<pre class="aibolit-logs" data-aibolit-logs="true">{$scan_log}</pre>
{/if}
{if !$module_log && !$scan_log}
    Нет записей
{/if}