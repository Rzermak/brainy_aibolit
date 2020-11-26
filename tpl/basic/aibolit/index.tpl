<script src="{$template_path}js/aibolit.js"></script>
<link rel="stylesheet" type="text/css" href="{$template_path}css/aibolit.css" />
<h2>Aibolit</h2>
<div id="complectation" class="status">
    <p>
        <b>Текущая версия баз:</b>
        <span data-aibolit-versionbase="true">
        {if $scanner_version}
            {$scanner_version}
        {else}
            Ошибка, требуется обновление
        {/if}
        </span>
    </p>
    <p>
        <b>Последнее обновление баз:</b>
        <span data-aibolit-versiontime="true">
        {if $aibolitconf.last_aibolit_update}
            {$aibolitconf.last_aibolit_update|date_format:"%Y-%m-%d %H:%M:%S"}
        {else}
            Никогда
        {/if}
        </span>
    </p>
    <p>
        <span class="aibolit_license">Внимание! Данный модуль основывается на работе антивируса <a href="https://revisium.com/ai/" target="_blank">Ai-Bolit</a>. Автор не несёт ответственности за приченённые неудобства или потери данных. Вы используете модуль на свой страх и риск.</span>
    </p>
</div>
<div class="tabs-menu-main tabs-menu">
    <span class="tabs-menu-active" data-tab-btn="web-server">Проверка</span>
    <span class="" data-tab-btn="report">Отчёты</span>
    <span class="" data-tab-btn="logs">Логи</span>
    <span class="" data-tab-btn="update">Обновление баз</span>
    <span class="" data-tab-btn="options">Настройки</span>
    <span class="" data-tab-btn="module-info">Информация</span>
</div>
<div class="wrap">
    <div data-tab="web-server" style="display: block;">
        <h3>Управление</h3>
        <div class="list-div">
            <div>
                <span data-command="start" data-aibolit-scan="true" data-site="all" class="btn btn-play">Сканировать всё</span>
                <span data-command="stop" data-aibolit-scan="true" data-site="all" class="btn btn-stop">Остановить всё</span>
                <span data-command="update-siteslist" class="btn btn-undo">Обновить список</span>
            </div>
        </div>
        <hr>
        <h3>Все сайты</h3>
        <div class="div-20" data-siteslist="true">
            <i class="fa fa-spinner fa-spin"></i> Пожалуйста подождите
        </div>
    </div>
    <div data-tab="report" style="display: none;">
        <h3>Сайт</h3>
        <div class="div-20">
            <select name="site_report" data-site-report="true">
                <option value="">Выберите сайт</option>
                {foreach from=$sites item=site}
                    <option value="{$site.domain}">{$site.domain}</option>
                {/foreach}
            </select>
        </div>
        <hr>
        <h3>Список заражённых файлов</h3>
        <div class="div-20" data-reportlist="true">
            Выберите сайт
        </div>
    </div>
    <div data-tab="logs" style="display: none;">
        <h3>Сайт</h3>
        <div class="div-20">
            <select name="site_logs" data-site-logs="true">
                <option value="">Выберите сайт</option>
                <option value="aibolit">Логи модуля</option>
                {foreach from=$sites item=site}
                    <option value="{$site.domain}">{$site.domain}</option>
                {/foreach}
            </select>
        </div>
        <hr>
        <h3>Логи</h3>
        <div class="div-20" data-logslist="true">
            Выберите сайт
        </div>
    </div>
    <div data-tab="update" style="display: none;">
        <h3>Обновить базу</h3>
        <div class="div-20">
            <span data-command="update-base" class="btn btn-undo">Запустить автоматическое обновление</span>
            <span data-autoupdate-result="true"></span>
        </div>
        <hr>
        <h3>Обновить базу по ссылке</h3>
        <div class="div-20">
            <form name="updateFromAddress" method="post" action="index.php?do=aibolit">
                <input type="hidden" name="subdo" value="update_from_address">
                <div class="list-div">
                    <label>Адрес архива антивируса</label>
                    <input type="text" name="archive_address" value="{$aibolitconf.last_archive_link}">
                    <button type="submit" class="btn btn-check">Начать обновление</button>
                </div>
                <span data-update-result="true"></span>
            </form>
            <br>Для получения ссылки перейдите по адресу <a href="https://revisium.com/ai/" target="_blank">https://revisium.com/ai/</a> и скопируйте её с кнопки "AI-Bolit для сайтов" (<a href="http://joxi.ru/KAx49BgCK64b8A" target="_blank">Скриншот</a>).
        </div>
        <hr>
        <h3>Обновить базу из архива</h3>
        <div class="div-20">
            <form name="updateFromFile" method="post" action="index.php?do=aibolit" enctype="multipart/form-data">
                <input type="hidden" name="subdo" value="update_from_file">
                <div class="list-div">
                    <label>Архив антивируса</label>
                    <input type="file" name="archive_scanner">
                    <button type="submit" class="btn btn-check">Загрузить и обновить</button>
                </div>
                <span data-update-result="true"></span>
            </form>
            <br>Для получения архива перейдите по адресу <a href="https://revisium.com/ai/" target="_blank">https://revisium.com/ai/</a> и скачайте его нажав на кнопку "AI-Bolit для сайтов" (<a href="http://joxi.ru/v296l37TpOGOvA" target="_blank">Скриншот</a>).
        </div>
        <hr>
        <h3>История версий текущей базы</h3>
        <div class="div-20">
            <pre class="aibolit-pre" data-aibolit-versions-history="true">{if $scanner_versions_history}{$scanner_versions_history}{else}Ошибка, возможно у вас отсутствует сканнер aibolit, требуется обновление{/if}</pre>
        </div>
    </div>
    <div data-tab="options" style="display: none;">
        <h3>Настройки</h3>
        <form name="aibolitConfig" method="post" action="index.php?do=aibolit&subdo=save_config">
            <div class="list-div">
                <div data-config="scan_fast_mode">
                    <label class="switch createindexhtml_onoff">
                        <input type="checkbox" name="options[scan_fast_mode]" value="1" {if $aibolitconf.scan_fast_mode eq '1'}checked=""{/if}>
                        <span class="slider round"></span>
                    </label>
                    <span>Экспресс-проверка <i data-text="Это проверка в обычном режиме (диагностика). Не рекомендуется для лечения сайта" class="fa fa-question-circle tip" aria-hidden="true"></i></span>
                </div>
                <div data-config="scan_memory">
                    <label>Максимально разрешено ОЗУ на поток</label>
                    <select name="options[scan_memory]">
                        {foreach from=$scan_memorys item=scan_memory}
                            <option value="{$scan_memory.id}" {if $aibolitconf.scan_memory == $scan_memory.id}selected{/if}>{$scan_memory.name}</option>

                        {/foreach}
                    </select>
                </div>
                <div data-config="max_thread">
                    <label>Максимальное кол-во потоков <i data-text="Максимальное количество одновременно сканируемых сайтов" class="fa fa-question-circle tip" aria-hidden="true"></i></label>
                    <input type="text" name="options[max_thread]" value="{$aibolitconf.max_thread}">
                </div>
                <div data-config="cron_type">
                    <label>Сканирование по рассписанию</label>
                    <select name="options[cron_type]">
                        {foreach from=$cron_types item=cron_type}
                            <option value="{$cron_type.id}" {if $aibolitconf.cron_type == $cron_type.id}selected{/if}>{$cron_type.name}</option>

                        {/foreach}
                    </select>
                </div>
                <div data-config="cron_time">
                    <label>Запускать сканирование в</label>
                    <select name="options[cron_time]">
                        {foreach from=$cron_times item=cron_time}
                            <option value="{$cron_time.id}" {if $aibolitconf.cron_time == $cron_time.id}selected{/if}>{$cron_time.name}</option>

                        {/foreach}
                    </select>
                </div>
                <div data-config="auto_update_base">
                    <label class="switch createindexhtml_onoff">
                        <input type="checkbox" name="options[auto_update_base]" value="1" {if $aibolitconf.auto_update_base eq '1'}checked=""{/if}>
                        <span class="slider round"></span>
                    </label>
                    <span>Автоматически обновлять антивирусные базы <i data-text="Только при автоматическом запуске через планировщик" class="fa fa-question-circle tip" aria-hidden="true"></i></span>
                </div>
                <div data-config="send_email_detected">
                    <label class="switch createindexhtml_onoff">
                        <input type="checkbox" name="options[send_email_detected]" value="1" {if $aibolitconf.send_email_detected eq '1'}checked=""{/if}>
                        <span class="slider round"></span>
                    </label>
                    <span>Уведомлять о вирусах и ошибках сканирования по email <i data-text="Только при автоматическом запуске через планировщик" class="fa fa-question-circle tip" aria-hidden="true"></i></span>
                </div>
                <div data-config="email">
                    <label>Email для уведомлений</label>
                    <input type="text" name="options[email]" value="{$aibolitconf.email}">
                </div>
                <div><button type="submit" class="btn btn-check">Применить</button></div>
            </div>
        </form>
    </div>
    <div data-tab="module-info" style="display: none;">
        <h3>Информация о модуле</h3>
        <div class="div-20">
            <p><b>Версия модуля:</b> {$module_version}</p>
            <p><b>Сайт модуля:</b> <a href="https://github.com/Rzermak/brainy_aibolit" target="_blank">https://github.com/Rzermak/brainy_aibolit</a></p>
            <p><b>Лицензия:</b> MIT License <a href="http://www.opensource.org/licenses/mit-license.html" target="_blank">http://www.opensource.org/licenses/mit-license.html</a></p>
        </div>
        <hr>
        <h3>Информация об антивирусе Ai-Bolit</h3>
        <div class="div-20">
            <p>
                <b>Версия:</b>
                <span data-aibolit-versionbase="true">
                {if $scanner_version}
                    {$scanner_version}
                {else}
                    Ошибка, требуется обновление
                {/if}
                </span>
            </p>
            <p><b>Сайт:</b> <a href="https://revisium.com/ai/" target="_blank">https://revisium.com/ai/</a></p>
        </div>
    </div>
</div>